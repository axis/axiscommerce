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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class ErrorController extends Axis_Core_Controller_Front
{
    public function errorAction()
    {
        //error_log('errorAction');
        $this->getResponse()->clearBody();
        $errors = $this->_getParam('error_handler');
        $exception = $errors->exception;

        // log all kind of errors
        try {
            $log = new Zend_Log(
                new Zend_Log_Writer_Stream(
                    Axis::config()->system->path .
                    Axis::config()->log->main->php
                )
            );
            $log->debug(
                $exception->getMessage() . "\n" .  $exception->getTraceAsString()
            );
        } catch (Zend_Log_Exception $e) {
            //who cares
        }

        switch ($errors->type) {
            //case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE: zf 1.10
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                return $this->_forward('not-found');
                //return $this->notFoundAction();
            default:
                // application error
                Zend_Debug::dump(Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName());
                Zend_Debug::dump(Axis_Area::isBackend());
                Zend_Debug::dump($exception->getMessage());
                Zend_Debug::dump($exception->getTraceAsString());
                return;
                $this->_helper->layout->setLayout('layout_error'); // hardcoded layout for application errors
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->pageTitle = Axis::translate('core')->__(
                    'An error has occured processing your request'
                );
                $this->view->meta()->setTitle($this->view->pageTitle);

                if (Axis::app()->getEnvironment() == 'development') {
//                    $traceAsString = preg_replace(
//                        '/(#\d+\s)(\/.*\/[^\/]+(?:\.php|\.phtml))/',
////                        "<a onclick=\"window.open('file://$2');return false;\">$1$2</a>",
//                        "<a href=\"file://$2\">$1$2</a>",
//                        $exception->getTraceAsString()
//                    );

                    $this->view->error = $exception->getMessage() .
                        "\n<strong>" . Axis::translate('core')->__('Trace') . ":</strong>\n"
                        . $this->view->escape($exception->getTraceAsString())
//                        . $traceAsString
                    ;
                }

                break;
        }
    }

    /**
     * 404 error controller or action not found
     */
    public function notFoundAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
        $this->view->pageTitle = Axis::translate('core')->__('Page not found');
        $this->view->meta()->setTitle('404 ' . $this->view->pageTitle);
        $this->addBreadcrumb(array(
            'label' => $this->view->pageTitle,
            'controller' => 'error',
            'route'      => 'core'
        ));
        $this->render('error/404', null, true);
    }
}