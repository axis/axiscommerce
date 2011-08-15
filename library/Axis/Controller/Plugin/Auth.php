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
 * @package     Axis_Controller
 * @subpackage  Plugin
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Plugin
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) 
    {
        if (Axis_Area::isBackend()) {
            
            $auth = Zend_Auth::getInstance();
            $auth->setStorage(new Zend_Auth_Storage_Session('admin'));

            $actionName     = $request->getActionName();
            $controllerName = $request->getControllerName();
            $moduleName     = $request->getModuleName();
            
            if (in_array($controllerName, array('auth', 'forgot'))
                && 'Axis_Admin' === $request->getModuleName()
            ) {
                return;
            }
            
            if (!$auth->hasIdentity()) {
                if ($request->isXmlHttpRequest()) {
                    Axis::message()->addError(
                        Axis::translate('admin')->__(
                            'Your session has been expired. Please relogin'
                    ));
                    $jsonHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                    $jsonHelper->sendFailure();
                    return;
                }
                
                $request->setModuleName('Axis_Admin')
                    ->setControllerName('auth')
                    ->setActionName('index');
                return;
            }
            
            $user = Axis::single('admin/user')->find($auth->getIdentity())
                ->current();
            
            if (!$user) {
                $request->setModuleName('Axis_Admin')
                    ->setControllerName('auth')
                    ->setActionName('logout');
                return;
            }
            
            //ACL
            $acl    = Axis::single('admin/acl');
            $roleId = Axis::session()->roleId;
            if (!empty($roleId)) {
                $acl->loadRules($roleId);
            }
            if (false === $acl->check($roleId, "admin/$controllerName/$actionName")) {
                if ($request->isXmlHttpRequest()) {
                    Axis::message()->addError(
                        Axis::translate('admin')->__(
                            'You have no permission for this operation'
                        )
                    );
                    $jsonHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                    $jsonHelper->sendFailure();
                    return;
                }
                $request->setModuleName('Axis_Admin')
                    ->setControllerName('informer')
                    ->setActionName('access-denied');
            }
        }
    }
}
