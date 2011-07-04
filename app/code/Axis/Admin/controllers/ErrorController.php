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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_ErrorController extends Axis_Admin_Controller_Back
{
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        $exception = $errors->exception;

        try {
            $log = new Zend_Log(new Zend_Log_Writer_Stream(Axis::config()->system->path . Axis::config()->log->main->php));
            $log->debug($exception->getMessage() . "\n" .  $exception->getTraceAsString());
        } catch (Zend_Log_Exception $e) {
            //who cares
        }

        $this->getResponse()->clearBody();

        if ($this->getRequest()->isXmlHttpRequest()) {
            Axis::message()->addError($exception->getMessage());
            $this->_helper->json->sendFailure();
        } else {
            $this->view->pageTitle = Axis::translate('admin')->__('Error');
            $this->view->error = str_replace("\n", "<br />\n", $exception->getMessage() . "\n"
             . $exception->getTraceAsString());
            $this->render();
        }
    }
}