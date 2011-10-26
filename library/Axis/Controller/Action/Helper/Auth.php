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

        if(Axis_Area::isFrontend()) {
            if (!Axis::getCustomerId()
                    && $this->getActionController() instanceof Axis_Account_Controller_Abstract) {

                $request->setModuleName('Axis_Account')
                    ->setControllerName('auth')
                    ->setActionName('index')
                    ->setDispatched(false);
            }
            return;
        }

        if (!Axis_Area::isBackend()) {
            return;
        }

        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('admin'));

        if (in_array($request->getControllerName(), array('auth', 'forgot'))
            && 'Axis_Admin' === $request->getModuleName()) {

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

        $acl = new Zend_Acl();
        // add resources
        $resources = Axis::model('admin/acl_resource')->toFlatTree();
        foreach ($resources as $resource) {
            $parent = $resource['parent'] ? $resource['parent'] : null;

            try {
                $acl->addResource($resource['id'], $parent);
            } catch (Zend_Acl_Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
        }

        //add role(s)
        $role = (string) $user->role_id;
        $acl->addRole($role);

        //add permission
        $rowset = Axis::single('admin/acl_rule')
            ->select('*')
            ->where('role_id = ?', $role)
            ->fetchRowset();
        foreach ($rowset as $row) {
            if (!$acl->has($row->resource_id)) {
                // $row->delete(); // remove invalid rule
                continue;
            }
            $action = 'deny';
            if ('allow' === $row->permission) {
                $action = 'allow';
            }
            try {
                $acl->$action($row->role_id, $row->resource_id);
            } catch (Zend_Acl_Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
        }

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($acl);
        Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole($role);

        if (in_array($request->getControllerName(), array('error'))
            && 'Axis_Admin' === $request->getModuleName()) {

            return;
        }

        //get current resource by request
        $request = $this->getRequest();
        $inflector = new Zend_Filter_Inflector();
        $resource = $inflector->addRules(array(
                 ':module'     => array('Word_CamelCaseToDash', new Zend_Filter_Word_UnderscoreToSeparator('/'), 'StringToLower'),
                 ':controller' => array('Word_CamelCaseToDash', 'StringToLower', new Zend_Filter_PregReplace('/admin_/', '')/*, new Zend_Filter_PregReplace('/\./', '-')*/),
                 ':action'     => array('Word_CamelCaseToDash', /* new Zend_Filter_PregReplace('#[^a-z0-9' . preg_quote('/', '#') . ']+#i', '-'), */'StringToLower'),
            ))
            ->setTarget('admin/:module/:controller/:action')
            ->filter($request->getParams());

        if (!$acl->has($resource)/*not found error*/
            || $acl->isAllowed($role, $resource)) {

            return;
        }
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
            ->setControllerName('error')
            ->setActionName('access-denied')
            ->setDispatched(false);
    }
}
