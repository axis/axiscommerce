<?php
/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category    Axis
 * @package     Axis_Mail
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Mail
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Mail extends Zend_Mail
{
    /**
     * Template renderer
     *
     * @var Zend_View
     */
    public $view;

    /**
     * Locale code. This locale is used during template rendering and subject translation
     *
     * @var string
     */
    protected $_locale = null;

    /**
     * Flag that is using for disable mail sending
     *
     * @var bool
     */
    protected $_disabled = false;

    /**
     *
     * @param string $charset
     */
    public function __construct($charset = 'UTF-8')
    {
        parent::__construct($charset);
        $this->view = Axis::app()->getBootstrap()->getResource('layout')->getView();
        $this->view->addScriptPath(Axis::config('system/path') . '/app/design/mail');
        $this->view->site    = Axis::getSite()->name;
        $this->view->company = Axis::single('core/site')->getCompanyInfo();
    }

   /**
    *
    * @todo refactoring http://www.zendcasts.com/painless-html-emails-with-zend_mail/2010/06/?utm_source=feedburner&utm_medium=feed&utm_campaign=Feed%3A+ZendScreencastsVideoTutorialsAboutTheZendPhpFrameworkForDesktop+%28Zend+Screencasts%3A+Video+Tutorials+about+the+Zend+PHP+Framework++%28desktop%29%29&utm_content=Google+Reader
    *
    * @param array
    * <pre>
    * array(
    *      'event'      => string                   [optional]
    *      'subject'    => string,
    *      'data'       => array(
    *          'text'   =>
    *          'blabla' =>
    *          ....
    *      ),
    *      'to'         => email,
    *      'from'       => mixed (string|array('email' => '', 'name' => '')), [optional]
    *      'type'       => string 'txt'|'html',     [optional]
    *      'charset'    => string                   [optional],
    *      'attachments' => array()
    *  )
    * </pre>
    *  @return bool
    */
    public function setConfig(array $config)
    {
        $from = array();
        if (isset($config['from']) && is_string($config['from'])) {
            $from = $config['from'];
        } else {
            if (isset($config['from']['email'])) {
                $from['email'] = $config['from']['email'];
            }
            if (isset($config['from']['name'])) {
                $from['name'] = $config['from']['name'];
            }
        }

        if (isset($config['event'])) {
            $mailTemplate = Axis::model('core/template_mail')->select('*')
                ->where('ctm.event = ?', $config['event'])
                ->fetchRow();

            if (!$mailTemplate || !$mailTemplate->status) {
                $this->_disabled = true;
                return false;
            }

            $siteIds = explode(',', $mailTemplate->site);
            if (isset($config['siteId'])) {
                if (!in_array($config['siteId'], $siteIds)) {
                    $this->_disabled = true;
                    return false;
                }
            } elseif (!in_array(Axis::getSiteId(), $siteIds)) {
                $this->_disabled = true;
                return false;
            }

            $type = isset($config['type']) ? $config['type'] : $mailTemplate->type;

            if (is_array($from)) {
                if (!isset($from['email'])) {
                    $from['email'] = Axis_Collect_MailBoxes::getName($mailTemplate->from);
                }
            }
        }

        $this->setSubject($config['subject']);
        $this->addTo($config['to']);
        $this->view->to = $config['to'];

        $siteName = $this->view->site;
        if (isset($config['siteId']) && $config['siteId'] != Axis::getSiteId()) {
            $siteName = Axis::model('core/site')->find($config['siteId'])
                ->current()
                ->name;
        }
        $this->view->siteName = $siteName;
        if (is_array($from)) {
            if (!isset($from['name'])) {
                $from['name'] = $siteName;
            }
            $this->setFrom($from['email'], $from['name']);
            $this->view->from = $from['email'];
        } else {
           $this->setFrom($from);
           $this->view->from = $from;
        }

        $this->view->assign($config['data']);

        if (isset($config['event'])) {
            if ('html' == $type) {
                $this->renderHtml($mailTemplate->template);
            } else {
                $this->renderText($mailTemplate->template);
            }
        } else {
            $this->setBodyText($config['data']['text']);
        }

        if (isset($config['attachments'])) {
            foreach ($config['attachments'] as $name => $file) {
                $attachment = $this->createAttachment($this->view->render($file));
                $attachment->filename = $name;
            }
        }

        return true;
    }

    /**
     *
     * @param string $template
     * @param array $data
     * @return Axis_Mail
     */
    public function renderText($template)
    {
        $this->setBodyText($this->view->render($template . '_txt.phtml'));
        return $this;
    }

    /**
     * Fluent interface
     * @param string $template
     * @param array $data
     * @return Axis_Mail
     */
    public function renderHtml($template)
    {
        $this->setBodyHtml($this->view->render($template . '_html.phtml'));
        return $this;
    }

    /**
     *
     * @return Zend_Mail_Transport_Abstract
     */
    public function getTransport()
    {
        if (null === self::$_defaultTransport) {
            $config = Axis::config();
            switch ($config->mail->main->transport) {
                case 'smtp':
                    $options = array(
                        'port' => intval($config->mail->smtp->port)
                    );
                    if ($config->mail->smtp->auth) {
                        $options['auth']     = 'login';
                        $options['username'] = $config->mail->smtp->user;
                        $options['password'] = $config->mail->smtp->password;
                        if ('none' != $config->mail->smtp->secure) {
                            $options['ssl'] = $config->mail->smtp->secure;
                        }
                    }

                    $transport = new Zend_Mail_Transport_Smtp(
                        $config->mail->smtp->host, $options
                    );
                    //$transport->EOL = "\r\n";    // gmail is fussy about this
                    break;

                case 'sendmail':
                default:
                    $transport = new Zend_Mail_Transport_Sendmail();
                    break;
            }
            self::setDefaultTransport($transport);
        }
        return self::$_defaultTransport;
    }

    /**
     * Sends this email using the given transport or a previously
     * set DefaultTransport or the internal mail function if no
     * default transport had been set.
     *
     * @param Zend_Mail_Transport_Abstract $transport
     * @return Axis_Mail
     */
    public function send($transport = null)
    {
        if (null !== $this->_locale) {
            $this->setLocale($this->_locale);
            $this->_locale = null;
        }

        if (true === $this->_disabled) {
            return $this;
        }

        if (null === $transport) {
            $transport = $this->getTransport();
        }

        return @parent::send($transport);
    }

    /**
     * Set the locale to be used for email template and subject
     * Locale will be automatically switched back after calling the send method
     *
     * @param string $locale Locale code
     */
    public function setLocale($locale)
    {
        $this->_locale = Axis_Locale::getLocale()->toString();
        Axis_Locale::setLocale($locale);
    }
}