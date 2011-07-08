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
 * @subpackage  Axis_Admin_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Acl_Role extends Axis_Db_Table
{
    protected $_name = 'admin_acl_role'; //aar
    protected $_rolesTree = null;

    private function _initTree()
    {
        if (null !== $this->_rolesTree) {
            return;
        }
        $stmt = $this->select('*')
            ->joinLeft('admin_acl_role_parent',
                'aarp.role_parent_id = aar.id',
                array('child_id' => 'role_id')
            )
            ->query();

        $rolesTree = array();

        while (($row = $stmt->fetch())) {
            if (!isset($rolesTree[$row['id']])) {
                $rolesTree[$row['id']] = $row;
            }
            if ($row['child_id']) {
                $rolesTree[$row['id']]['childs'][] = $row['child_id'];
            }
        }

        foreach ($rolesTree as $id => $item) {
            if (!isset($item['childs'])) {
                continue;
            }
            foreach ($item['childs'] as  $child) {
                if (!isset($rolesTree[$child]['parents'])) {
                    $rolesTree[$child]['parents'] = array();
                }
                $rolesTree[$child]['parents'][] = $id;
            }
        }

        $this->_rolesTree = $rolesTree;
    }

    public function getRolesTree()
    {
        $this->_initTree();
        return $this->_rolesTree;
    }

    public function getAllChilds($id)
    {
        $this->_initTree();
        if (isset($this->_rolesTree[$id]['childs'])
            && sizeof($this->_rolesTree[$id]['childs'])) {

            $childs = $this->_rolesTree[$id]['childs'];
            $subchilds = array();
            foreach ($childs as $childId) {
                $subchilds = array_merge(
                    $subchilds, $this->getAllChilds($childId)
                );
            }
            return array_merge($childs, $subchilds);
        }

        return array();
    }

    public function getAllParents($id)
    {
        $this->_initTree();
        if (isset($this->_rolesTree[$id]['parents'])
            && sizeof($this->_rolesTree[$id]['parents'])) {

            $parents = $this->_rolesTree[$id]['parents'];
            $subparents = array();
            foreach ($parents as $parentId) {
                $subparents = array_merge(
                    $subparents, $this->getAllParents($parentId)
                );
            }
            return array_merge($parents, $subparents);
        }

        return array();
    }

    public function getPossibleParents($id)
    {
        $childRoleIds = $this->getAllChilds($id, $this->getRolesTree());
        $possibleParrentRoles = $this->getRolesTree();
        unset($possibleParrentRoles[$id]);
        foreach ($childRoleIds as $id) {
            unset($possibleParrentRoles[$id]);
        }
        return $possibleParrentRoles;
    }

    /**
     *
     * @param int $roleId
     * @return array
     */
    public function getParents($roleId)
    {
        return Axis::single('admin/acl_role_parent')->select('role_parent_id')
            ->where('role_id = ?', $roleId)
            ->fetchCol();
    }

    public function getName($id)
    {
        $this->_initTree();
        return $this->_rolesTree[$id]['role_name'];
    }

    public function getRules($roleId)
    {
        return Axis::single('admin/acl_rule')->select(
                array('resource_id', 'permission')
            )
            ->where('role_id = ?', $roleId)
            ->fetchAll();
    }
}