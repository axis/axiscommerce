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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_RolesController extends Axis_Admin_Controller_Back
{

    /**
     * Acl Model
     *
     * @var Axis_Admin_Model_Acl_Role
     */
    private $_roles;

    public function init()
    {
        parent::init();
        $this->_roles = Axis::single('admin/acl_role');
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Roles');
        $this->view->resources = Axis::single('admin/acl_resource')->getTree();
        $this->render();
    }

    public function getNodesAction()
    {
        $nodes = $this->_roles->fetchAll();
        foreach ($nodes as $item) {
            $result[] = array(
                'text' => $item->role_name,
                'id'   => $item->id,
                'leaf' => false,
                'children' => array(),
                'expanded' => true
            );
        }

        $this->_helper->json->sendJson($result, false, false);
    }

    public function getParentAllowsAction()
    {
        $parents = Zend_Json::decode($this->_getParam('parents'));
        if (!count($parents)) {
            return $this->_helper->json->sendJson(array(), false, false);
        }
        $allows = $this->acl->getParentRolesAllows($parents);
        $this->_helper->json->sendJson($allows, false, false);
    }

    public function addAction()
    {
        $id = $this->_roles->insert(array(
            'role_name' => $this->_getParam('roleName')
        ));
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Role was added successfully'
            )
        );
        $this->_helper->json->sendJson(array(
            'success' => $id,
            'id'      => $id
        ));
    }

    public function editAction()
    {
        $roleId = $this->_getParam('id');

        $role = $this->_roles->find($roleId)->current();

        $result = array(
            'id'   => $role->id,
            'name' => $role->role_name
        );

        $result['possibleParents'] = (object) $this->_roles->getPossibleParents($roleId);
        $result['parents'] = $this->_roles->getParents($roleId);

        $result['parentAllows'] =
            $this->acl->getParentRolesAllows($result['parents']);
        $result['rules'] = $this->_roles->getRules($roleId);
        $this->_helper->json->sendSuccess(array(
            'data' => $result
        ));
    }

    public function deleteAction()
    {
        $roleId = $this->_getParam('roleId');
        $this->_roles->delete($this->db->quoteInto('id = ?', $roleId));
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Role was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $roleId = $this->_getParam('roleId');

        $this->_roles->update(
            array('role_name' => $this->_getParam('roleName')),
            $this->db->quoteInto('id = ?', $roleId)
        );

        /* save parent roles */
        $parents = $this->_getParam('role', array());
        $this->_roles->saveParents($roleId, $parents);

        /* save rules */
        $rules = $this->_getParam('rule');
        $this->_roles->saveRules($roleId, $rules);

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Role was saved successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }
}