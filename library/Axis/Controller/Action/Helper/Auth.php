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
 * @subpackage  Axis_Controller_Action_Helper
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Action_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Action_Helper_Auth extends Zend_Controller_Action_Helper_Abstract
{
    public function preDispatch()
    {
        $request = $this->getRequest();
        if (Axis_Area::isBackend()) {
            $this->_backAuth($request);
        } elseif(Axis_Area::isFrontend()) {
            $this->_frontAuth($request);
        }
    }
    
    protected function _backAuth($request) 
    {
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
                ->setActionName('index')
                ->setDispatched(false);
            return;
        }

        $user = Axis::single('admin/user')->find($auth->getIdentity())
            ->current();

        if (!$user) {
            $request->setModuleName('Axis_Admin')
                ->setControllerName('auth')
                ->setActionName('logout')
                ->setDispatched(false);
            return;
        }
        
        //ACL
        $modelAcl    = Axis::single('admin/acl');
        $roleId = Axis::session()->roleId;
        if (!empty($roleId)) {
            $modelAcl->loadRules($roleId);
        }
        $viewRenderrer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $inflector = $viewRenderrer->getInflector();
        $params = array(
            'module'     => $request->getModuleName(),
            'controller' => $controllerName,
            'action'     => $actionName
        );
        $inflector->setTarget('admin/:module/:controller/:action');
        $resource = $inflector->filter($params);
        
        if (false === $modelAcl->check($roleId, $resource)) {
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
                ->setActionName('access-denied')
                ->setDispatched(false);
        }
    }
    
    protected function _frontAuth($request) 
    {
        if (!Axis::getCustomerId() 
                && $this->getActionController() instanceof Axis_Account_Controller_Abstract) {
                
                $request->setModuleName('Axis_Account')
                    ->setControllerName('auth')
                    ->setDispatched(false);
            }
    }
}
