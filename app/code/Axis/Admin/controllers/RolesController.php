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
class Axis_Admin_RolesController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Roles');
        $this->view->resources = Axis::single('admin/acl_resource')->getTree();
        $this->render();
    }

    public function getNodesAction()
    {
        foreach (Axis::model('admin/acl_role')->fetchAll() as $row) {
            $result[] = array(
                'text'     => $row->role_name,
                'id'       => $row->id,
                'leaf'     => false,
                'children' => array(),
                'expanded' => true
            );
        }

        $this->_helper->json->sendRaw($result);
    }

    public function getParentAllowsAction()
    {
        $parents = Zend_Json::decode($this->_getParam('parents'));
        $allows = array();
        if (count($parents)) {
            $allows = $this->acl->getParentRolesAllows($parents);
        }
        $this->_helper->json->sendRaw($allows);
    }

    public function addAction()
    {
        $role = Axis::model('admin/acl_role')->createRow();
        $role->role_name = $this->_getParam('roleName');
        $role->save();

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Role was added successfully'
            )
        );
        $this->_helper->json->setId($role->id)
            ->sendSuccess();
    }

    public function editAction()
    {
        $roleId = $this->_getParam('id');
        $model = Axis::model('admin/acl_role');

        $role = $model->find($roleId)->current();

        $parents = $model->getParents($roleId);
        $data = array(
            'id'              => $role->id,
            'name'            => $role->role_name,
            'possibleParents' => (object) $model->getPossibleParents($roleId),
            'parents'         => $parents,
            'parentAllows'    => $this->acl->getParentRolesAllows($parents),
            'rules'           => $model->getRules($roleId)
        );

        $this->_helper->json->setData($data)
            ->sendSuccess();
    }

    public function deleteAction()
    {
        $roleId = $this->_getParam('roleId');
        Axis::model('admin/acl_role')->delete(
            $this->db->quoteInto('id = ?', $roleId)
        );
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

        $model       = Axis::model('admin/acl_role');
        $modelParent = Axis::model('admin/acl_role_parent');
        $modelRule   = Axis::model('admin/acl_rule');

        $roleId = $this->_getParam('roleId');
        $role   = $model->find($roleId)->current();
        $role->role_name = $this->_getParam('roleName');
        $role->save();

        /* save parent roles */
        $parents = $this->_getParam('role', array());
        $modelParent->delete(
            $this->db->quoteInto('role_id = ?', $role->id)
        );
        foreach ($parents as $parentId) {
            $modelParent->createRow(array(
                'role_id'        => $roleId,
                'role_parent_id' => $parentId
            ))->save();
        }

        /* save rules */
        $rules = $this->_getParam('rule');
        $modelRule->delete(
            $this->db->quoteInto('role_id = ?', $role->id)
        );

        $allow = isset($rules['allow']) ? $rules['allow'] : array();
        foreach ($allow as $resourceId) {
            $modelRule->createRow(array(
                'role_id'     => $roleId,
                'resource_id' => $resourceId,
                'permission'  => 'allow'
            ))->save();
        }

        $deny = isset($rules['deny']) ? $rules['deny'] : array();
        foreach ($deny as $resourceId) {
            $row = $modelRule->getRow($roleId, $resourceId);
            $row->permission = 'deny';
            $row->save();
        }

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Role was saved successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }
}