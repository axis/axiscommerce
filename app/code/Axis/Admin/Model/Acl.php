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
 * @copyright   Copyright 2008-2010 Axis
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
    private $_rescs;



    public function __construct()
    {
        $this->_loadResources();
    }

    protected function _loadResources()
    {
        foreach ($this->_getResources() as $resource) {
            if (false !== ($pos = strrpos($resource['resource_id'], '/'))) {
                $parentId = substr($resource['resource_id'], 0, $pos);
            } else {
                $parentId = null;
            }
            $this->add(new Zend_Acl_Resource($resource['resource_id']), $parentId);
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
        $this->addRoleRecursive($role);
        
        $rolesForLoad = Axis::single('admin/acl_role')->getAllParents($roleId);
        $rolesForLoad[] = $roleId;
        
        $stmt = Axis::single('admin/acl_rule')
            ->select('*')
            ->where('role_id IN(?)', $rolesForLoad)
            ->query()
            ;

        while ($row = $stmt->fetch()) {
            if ($row['permission'] == 'allow') {
                $this->allow($row['role_id'], $row['resource_id']);
            } elseif ($row['permission'] == 'deny') {
                $this->deny($row['role_id'], $row['resource_id']);
            }
        }
    }
    
    /**
     * Add role with all parent roles
     * 
     * @param Zend_Acl_Role_Interface|string $role
     */
    public function addRoleRecursive($role)
    {
        if ($role instanceof Zend_Acl_Role_Interface) {
            $roleId = $role->getRoleId();
        } else {
            $roleId = (string) $role;
        }
        
        $rolesTree = Axis::single('admin/acl_role')->getRolesTree();
        if (isset($rolesTree[$roleId]['parents'])) {
            foreach ($rolesTree[$roleId]['parents'] as $parentRoleId) {
                $this->addRoleRecursive($parentRoleId);
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
     *
     * @return array
     */
    protected function _getResources()
    {
        if (null === $this->_rescs) {
            $this->_rescs = Axis::single('admin/acl_resource')->select()
                    ->order('resource_id ASC')
                    ->fetchAll();
        }
        return $this->_rescs;
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

        foreach ($this->_getResources() as $resource) {
            if ($this->isAllowed('tmp', $resource['resource_id'])) {
                $allows[] = $resource['resource_id'];
            }
        }

        $this->removeRole('tmp');
        
        return $allows;
    }
}