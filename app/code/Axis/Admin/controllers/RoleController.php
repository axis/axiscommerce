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
class Axis_Admin_RoleController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Roles');
        $this->view->resources = Axis::single('admin/acl_resource')->getTree();
        $this->render();
    }
 
    public function listAction()
    {
        foreach (Axis::model('admin/acl_role')->fetchAll() as $row) {
            $data[] = array(
                'text'     => $row->role_name,
                'id'       => $row->id,
                'leaf'     => false,
                'children' => array(),
                'expanded' => true
            );
        }

        return $this->_helper->json->sendRaw($data);
    }
    
    public function loadAction()
    {
        $roleId   = $this->_getParam('id');
        $model    = Axis::model('admin/acl_role');
        $modelAcl = Axis::single('admin/acl');

        $row = $model->find($roleId)->current();

        $parents = $model->getParents($roleId);
        $data = array(
            'id'              => $row->id,
            'name'            => $row->role_name,
            'possibleParents' => (object) $model->getPossibleParents($roleId),
            'parents'         => $parents,
            'parentAllows'    => $modelAcl->getParentRolesAllows($parents),
            'rules'           => $model->getRules($roleId)
        );

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
    
    public function saveAction()
    {
        $model       = Axis::model('admin/acl_role');
        $modelParent = Axis::model('admin/acl_role_parent');
        $modelRule   = Axis::model('admin/acl_rule');

        $roleId = $this->_getParam('roleId');
        $row    = $model->find($roleId)->current();
        $row->role_name = $this->_getParam('roleName');
        $row->save();

        /* save parent roles */
        $parents = $this->_getParam('role', array());
        $modelParent->delete(
            $this->db->quoteInto('role_id = ?', $row->id)
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
            $this->db->quoteInto('role_id = ?', $row->id)
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
        return $this->_helper->json->sendSuccess();
    }

    public function addAction()
    {
        $row = Axis::model('admin/acl_role')->createRow();
        $row->role_name = $this->_getParam('roleName');
        $row->save();

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Role was added successfully'
            )
        );
        return $this->_helper->json
            ->setId($row->id)
            ->sendSuccess();
    }

    public function removeAction()
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
        return $this->_helper->json->sendSuccess();
    }

    public function listParentAction()
    {
        $parents = Zend_Json::decode($this->_getParam('parents'));
        $data = array();
        if (count($parents)) {
            $data = Axis::single('admin/acl')->getParentRolesAllows($parents);
        }
        return $this->_helper->json->sendRaw($data);
    }
}