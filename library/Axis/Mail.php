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
     *
     * @param string $charset
     */
    public function __construct($charset = 'UTF-8')
    {
        parent::__construct($charset);
//        $this->view = new Zend_View();
        $this->view = Axis::app()->getBootstrap()
            ->getResource('layout')
            ->getView();

        /* for use in ->render('../[script.php]')  */
        $this->view->setLfiProtection(false);

        $this->view->addScriptPath(
            Axis::config('system/path') . '/app/design/mail'
        );
        $sites               = Axis_Collect_Site::collect();
        $this->view->site    = $sites[Axis::getSiteId()];
        $this->view->company = Axis::single('core/site')->getCompanyInfo();
    }

   /**
    *
    * @todo refactoring http://www.zendcasts.com/painless-html-emails-with-zend_mail/2010/06/?utm_source=feedburner&utm_medium=feed&utm_campaign=Feed%3A+ZendScreencastsVideoTutorialsAboutTheZendPhpFrameworkForDesktop+%28Zend+Screencasts%3A+Video+Tutorials+about+the+Zend+PHP+Framework++%28desktop%29%29&utm_content=Google+Reader
    *
    * @param array
    * <pre>
    * array(
    *      ['event' => string]
    *      'subject' => string,
    *      'data' => array(
    *          'text' =>
    *          'blabla' =>
    *          ....
    *      ),
    *      'to' => email,
    *      ['from' => mixed (string(email) or array('email', 'name'))],
    *      ['type' => enum('txt', 'html')],
    *      ['charset' =>   ],
    *      'report' => true|false
    *  )
    * </pre>
    *  @return bool
    */
    public function setConfig(array $config)
    {
        if (isset($config['event'])) {
            $mailTemplateId = Axis::single('core/template_mail')
                ->getIdByEvent($config['event']);
            if (false === $mailTemplateId) {
                return false;
            }
            $mailTemplate = Axis::single('core/template_mail')
                ->find($mailTemplateId)->current()->toArray();
            if (!is_array($mailTemplate)
                || !count($mailTemplate)
                || !$mailTemplate['status']) {

                return false;
            }
            $type = $mailTemplate['type'];
            if (isset($config['type'])) {
                $type = $config['type'];
            }
            $from = array('email' => Axis_Collect_MailBoxes::getName(
                $mailTemplate['from']), 'name' => $mailTemplate['name']
            );

            $siteIds = explode(',', $mailTemplate['site']);

            if (isset($config['siteId'])
                && !in_array(Axis::getSiteId(), $siteIds)) {

                return false;
            }
        }
        if (isset($config['from']['email'])) {
            $from['email'] = $config['from']['email'];
        }
        if (isset($config['from']['name'])) {
            $from['name'] = $config['from']['name'];
        }
        if (isset($config['from']) && is_string($config['from'])) {
            $from = $config['from'];
        }

        $this->setSubject($config['subject']);
        $this->view->subject = $config['subject'];
        $this->addTo($config['to']);
        if (is_array($from)){
            $this->setFrom($from['email'], $from['name']);
            $this->view->from = $from['email'];
        } else {
           $this->setFrom($from);
           $this->view->from = $from;
        }
        if (isset($config['event'])) {
            if ($type == 'html') {
                $this->renderHtml($mailTemplate['template'], $config['data']);
            } else {
                $this->renderText($mailTemplate['template'], $config['data']);
            }
        } else {
            $this->setBodyText($config['data']['text']);
        }
        return true;
    }

    /**
     *
     * @param string $template
     * @param array $data
     * @return Axis_Mail
     */
    public function renderText($template, array $data)
    {
        $this->view->assign($data);
        $this->setBodyText(
            $this->view->render(strval($template) . '_txt.phtml')
        );
        return $this;
    }

    /**
     * Fluent interface
     * @param string $template
     * @param array $data
     * @return Axis_Mail
     */
    public function renderHtml($template, array $data)
    {
        $this->view->assign($data);
        $this->setBodyHtml(
            $this->view->render(strval($template) . '_html.phtml')
        );
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
     *
     * @param Zend_Mail_Transport_Abstract $transport
     * @param bool $report
     * @return bool
     */
    public function send($transport = null, $report = true)
    {
        if (null === $transport) {
            $transport = $this->getTransport();
        }

        try {
            @parent::send($transport);
            if ($report) {
                Axis::message()->addSuccess(
                    Axis::translate('core')->__(
                        'Mail was sended successfully'
                    )
                );
            }
        } catch (Zend_Mail_Transport_Exception $e) {
            if ($report) {
                Axis::message()->addError(
                    Axis::translate('core')->__(
                        'Mail sending was failed.'
                    ) . ' ' . $e->getMessage()
                );
            }
            error_log($e->getMessage());
            return false;
        }
        return true;
    }
}