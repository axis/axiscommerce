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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Page extends Axis_Db_Table
{
    /**
     * The default table name 
     */
    protected $_name = 'core_page';

    /**
     *
     * @return int
     */
    public function getCount()
    {
        return $this->select()->count();
    }

    /**
     *
     * @param bool $likeString [optional]
     * @return array
     */
    public function getPages($likeString = false)
    {
        $pages = array();
        $pagesRowset = $this->fetchAll(
            null, array('module_name', 'controller_name', 'action_name')
        )->toArray();
        foreach ($pagesRowset as $page) {
            if ($likeString){
                $pages[$page['id']] = $page['module_name'] . '/' 
                     . $page['controller_name'] . '/'
                     . $page['action_name'];
            } else {
                $pages[$page['id']] = array(
                    'module' => $page['module_name'],
                    'controller' => $page['controller_name'],
                    'action' => $page['action_name']
                );
            }
        }
        return $pages;
    }
    
    /**
     * call in library/Axis/Layout.php
     * @param string module name
     * @param string controller name
     * @param string action name 
     * @return array
     */
    public function getPagesByRequest($module = '*', $controller = '*', $action = '*')
    {
        if (strpos($module, '/')) {
            list($module, $controller, $action) = explode('/', $module, 3);
        }
        
        return $this->select()
                ->where("module_name IN('*', ?)", $module)
                ->where("controller_name IN('*', ?)", $controller)
                ->where("action_name IN('*', ?)", $action)
                ->fetchAssoc();
    }

    /**
     *
     * @param string $request [optional]
     * @return string
     */
    public function getIdByPage($request = '*/*/*')
    {
        $request = explode('/', $request);
        $module = $request[0];
        $controller = isset($request[1]) ? $request[1] : '*';
        $action = isset($request[2]) ? $request[2] : '*';

        return $this->select('id')
            ->where('module_name = ?', $module)
            ->where('controller_name = ?', $controller)
            ->where('action_name = ?', $action)
            ->fetchOne();
    
    }
    
    /**
     * Remove pages 
     * Provide fluent interface
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return int
     */
    public function remove($module = '*', $controller = '*', $action = '*')
    {
        if (strpos($module, '/')) {
            $request    = explode('/', $module);
            $module     = $request[0];
            $controller = $request[1];
            $action     = $request[2];
        }
        
        $this->delete(
            "module_name = '{$module}' AND controller_name = '{$controller}' AND action_name = '{$action}'"
        );
        return $this;
    }
    
    /**
     * Save or insert page data
     * 
     * @param array $data ('module_name' =>, 'controller_name' =>, 'action_name' =>)
     * @return bool
     */
    public function save($data)
    {
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
            ));
            return false;
        }
        
        foreach ($data as $id => $row) {
            if (isset($row['id'])) {
                $this->update(
                    array(
                        'module_name'     => $row['module_name'],
                        'action_name'     => $row['action_name'],
                        'controller_name' => $row['controller_name']
                    ), 
                    'id = ' . intval($id)
                );
            } else {
                $this->insert(array(
                    'module_name'     => $row['module_name'],
                    'action_name'     => $row['action_name'],
                    'controller_name' => $row['controller_name']
                ));
            }
        }

        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return true;
    }
    
    /**
     * Add page if not exist
     * Provide fluent interface
     * @return bool
     */
    public function add($request = '*/*/*')
    {
        if ($this->getIdByPage($request)) {
            return $this;
        }
        $request = explode('/', $request);
        
        $this->insert(array(
            'module_name'     => $request[0],
            'controller_name' => $request[1],
            'action_name'     => $request[2]
        ));
        return $this;
    }
}
