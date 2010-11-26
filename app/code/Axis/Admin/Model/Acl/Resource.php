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
class Axis_Admin_Model_Acl_Resource extends Axis_Db_Table
{
    protected $_name = 'admin_acl_resource';
    
    /**
     * Get resource tree
     *
     * @return  array
     */
    public function getTree()
    {
        $tree = array();
        foreach ($this->fetchAll(null, 'resource_id') as $resource) {
            /*if (($pos = strrpos($resource->resource_id, '_')) !== false) {
                $parentId = substr($resource->resource_id, 0, $pos);
            } else*/if(($pos = strrpos($resource->resource_id, '/')) !== false){
                $parentId = substr($resource->resource_id, 0, $pos);
            } else {
                $parentId = '';
            }
            $tree[$parentId][$resource->resource_id] = $resource->title;
        }
        return $tree;
    }
    
    /**
     * Add resource
     *
     * @param string $resource
     * @param string $title[optional]
     * @return Axis_Admin_Model_Acl_Resource Provides fluent interface
     */
    public function add($resource, $title = null) 
    {
        if (null === $title) {
            $title = $resource;
        }
        //$resource = str_replace('_', '/', $resource);
        
        if ($this->select('id')->where('resource_id = ?', $resource)->fetchOne()) {
            //Axis::message()->addWarning(
            //  Axis::translate('admin')->__(
            //      "Resource %s already exist", $resource
            //  )
            //);
            return $this;
        }
        $row = $this->createRow(array(
            'resource_id' => $resource, 
            'title' => $title
        ));
        $row->save();
        
        return $this;
    }
    /**
     *
     * @param  string $resource
     * @return Axis_Admin_Model_Acl_Resource Provides fluent interface
     */
    public function remove($resource)
    {
        $this->delete("resource_id LIKE '{$resource}%'");
        return $this;
    }
}