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
class Axis_Admin_Model_Acl extends Zend_Acl
{

    public function __construct()
    {
        $resources = Axis::model('admin/acl_resource')->toFlatTree();
        foreach ($resources as $resource) {
            $parent = $resource['parent'] ? $resource['parent'] : null;
            
            try {
                $this->addResource($resource['id'], $parent);
            } catch (Zend_Acl_Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
        }
    }

    /**
     * Load rules of $role and all parent roles
     *
     * @param Zend_Acl_Role_Interface|string $role
     * @return boolean
     */
    public function loadRules($role)
    {
        if ($role instanceof Zend_Acl_Role_Interface) {
            $roleId = $role->getRoleId();
        } else {
            $roleId = (string) $role;
        }
        $this->_addRoleRecursive($role);

        $roles = Axis::single('admin/acl_role')->getAllParents($roleId);
        $roles[] = $roleId;
        
        $rules = Axis::single('admin/acl_rule')
            ->select('*')
            ->where('role_id IN(?)', $roles)
            ->fetchRowset()
            ;
        foreach ($rules as $rule) {
            $action = 'deny';
            if ('allow' === $rule->permission) {
                $action = 'allow';
            } 
            $this->$action($rule->role_id, $rule->resource_id);
        }
    }

    /**
     * Add role with all parent roles
     *
     * @param Zend_Acl_Role_Interface|string $role
     */
    protected function _addRoleRecursive($role)
    {
        if ($role instanceof Zend_Acl_Role_Interface) {
            $roleId = $role->getRoleId();
        } else {
            $roleId = (string) $role;
        }

        $rolesTree = Axis::single('admin/acl_role')->getRolesTree();
        if (isset($rolesTree[$roleId]['parents'])) {
            foreach ($rolesTree[$roleId]['parents'] as $parentRoleId) {
                $this->_addRoleRecursive($parentRoleId);
            }
        }
        if (!$this->hasRole($roleId))
            $this->addRole(
                new Zend_Acl_Role($roleId),
                isset($rolesTree[$roleId]['parents']) ?
                    $rolesTree[$roleId]['parents'] : null
            );
    }

    /**
     * Return allows array for gived roles as it will be parent roles
     *
     * @param $roles array
     * @return array
     */
    public function getParentRolesAllows(array $roles)
    {
        /*
         * Load rules
         */
        foreach ($roles as $role) {
            $this->loadRules($role);
        }

        /*
         * Create tmp role that inherit from $roles
         */
        $this->addRole(new Zend_Acl_Role('tmp'), $roles);

        $allows = array();

        $resources = Axis::model('admin/acl_resource')->toFlatTree();
        foreach ($resources as $resource) {
            if ($this->isAllowed('tmp', $resource['id'])) {
                $allows[] = $resource['id'];
            }
        }

        $this->removeRole('tmp');

        return $allows;
    }
    
    /**
     * @param int $role
     * @param string $resource
     * @return bool 
     */
    public function check($role, $resource) 
    {
        $resourceIds = explode('/', $resource);
        while (count($resourceIds)) {
            $resourceId = implode('/', $resourceIds);
            if ($this->has($resourceId)) {
                if (!$this->isAllowed($role, $resourceId)) {
                    break;
                } 
                return true;
            }
            array_pop($resourceIds);
        }
        return false;
    }
}