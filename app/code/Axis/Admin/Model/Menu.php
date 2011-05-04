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
class Axis_Admin_Model_Menu extends Axis_Db_Table 
{
    /**
     * The default table name 
     */
    protected $_name = 'admin_menu';
    
    /**
     * Return menu items array
     * @return array
     */
    public function getList()
    {   
        return $this->select()->fetchAssoc();
    }
    
    /**
     * Get current Sort Order
     * 
     * @param int $parent_id
     * @return int
     */
    private function _getCurrentSortOrder($parent_id = null) 
    {
        $max = 0;
        foreach($this->fetchAll()->toArray() as $item) {
            if ($item['parent_id'] != $parent_id) {
                continue;
            }
            if ($max < $item['sort_order'] ) {
                $max = $item['sort_order'];
            }
        }
        return $max + 10;   
    }
    
    /**
     * Get parent id by menu array 
     * @param array $menuPath
     * @return mixed (int|bool)
     */
    private function _getParentIdByMenu(array $menu) 
    {
        array_pop($menu);
        $prevId = null;
        foreach($menu as $item) {
            $currentId = $this->getIdByTitle($item); 
            $currentParentId = $this->getParentIdByTitle($item);
            
            if ($currentId && $prevId == $currentParentId) {
                $prevId = $currentId;
            } else {
                return false;
            }
        }
        return $currentId;
    }
    
    /**
     * Add new menu Item 
     * 
     * @param string $path "rootMenu->submenu->item"
     * @param string $link "/admin/submodule_controller/action"
     * @param int $sortOrder 10 
     * @return Axis_Admin_Model_Menu Provides fluent interface
     */
    public function add($path = '', $link = null, $sortOrder = null, $translationModule = null)
    {
        $menu_items = explode('->', $path);
        $check_before_insert = true;
        $data = array(
            'parent_id' => null,
            'lvl' => 0
        );
        $translationModule = null === $translationModule ?
            new Zend_Db_Expr('NULL') : $translationModule;
        
        foreach ($menu_items as $item) {
            $data['title'] = $item;
            
            if ($check_before_insert) {
                $rowMenu = $this->select()
                    ->where('title = ?', $data['title']);
                if (null === $data['parent_id']) {
                    $rowMenu->where('parent_id IS NULL');
                } else {
                    $rowMenu->where('parent_id = ?', $data['parent_id']);
                }    
                $rowMenu = $rowMenu->fetchRow();
                if ($rowMenu) {
                    if (isset($menu_items[$data['lvl'] + 1]) 
                        && $rowMenu->has_children == 0) {
                        
                        $rowMenu->has_children = 1;
                        $rowMenu->save();
                    }
                    $data['parent_id'] = $rowMenu->id;
                    $data['lvl']++;
                    continue;
                } else {
                    $check_before_insert = false;
                }
            }
            
            $rowMenu = $this->createRow();
            
            if (isset($menu_items[$data['lvl'] + 1])) {
                $data['has_children'] = 1;
                $data['link'] = null;
                $data['sort_order'] = $this->_getCurrentSortOrder($data['parent_id']);
                $data['translation_module'] = null;
            } else {
                $data['has_children'] = 0;
                $data['link'] = $link;
                $data['sort_order'] = (null === $sortOrder ? 
                    $this->_getCurrentSortOrder($data['parent_id']) : $sortOrder);
                $data['translation_module'] = $translationModule;
            }
            
            
            $rowMenu->setFromArray($data)->save();
            
            $data['parent_id'] = $rowMenu->id;
            $data['lvl']++;
        }
        
        return $this;    
    }
    
    /**
     * Edit menu item values like as title, link, Sort Order
     * @return Axis_Admin_Model_Menu Provides fluent interface
     */
    public function edit($path = '', $title = null, $link = null, $sortOrder = null)
    {
        $menuPath = explode('->', $path);
        $oldTitle = $menuPath[count($menuPath) - 1];
        $row = $this->find($this->getIdByTitle($oldTitle))->current();
        if (!row) {
            throw new Axis_Exception("Menu item with path : \"{$path}\" not exist");
        }
        if (null !== $title) {
            $row->title = $title;
        }
        if (null !== $link) {
            $row->link = $link;
        }
        if (null !== $sortOrder) {
            $row->sort_order = $sortOrder;
        }
        $row->save();
        return $this;
    }
     
    /**
     * Remove menu Item
     * Provide fluent interface
     * @param string $path "rootMenu->submenu->item"
     * @return Axis_Admin_Model_Menu Provides fluent interface
     */
    public function remove($path = '')
    {
        $menuPath = explode('->', $path);
        $title = $menuPath[count($menuPath) - 1];
        $parentId = $this->getParentIdByTitle($title);
        $this->find($this->getIdByTitle($title))->current()->delete();
        
        if (null !== $parentId && null === $this->getIdByParentId($parentId)) {
            $row = $this->find($parentId)->current();
            if ($row->link == '#') {
                $row->delete();
            } else {
                $row->has_children = 0;
                $row->save();
            }
        }
        return $this;
    }
    
}
