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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Template_Layout_Page extends Axis_Db_Table
{
    protected $_name = 'core_template_layout_page';
    
    /**
     * Save or insert layout_to_page assignments
     * 
     * @param int $templateId
     * @param array $data
     * @return bool
     */
    public function save($templateId, $data)
    {
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
            ));
            return false;
        }
        
        foreach ($data as $id => $values) {
            $rowData = array(
                'page_id'  => $values['page_id'] == '' ?
                    new Zend_Db_Expr('NULL') : $values['page_id'],
                'layout'   => $values['layout'],
                'priority' => $values['priority']
            );
            if (!isset($values['id']) 
                || !$row = $this->find($values['id'])->current()) {
                
                $rowData['template_id'] = $templateId;
                $row = $this->createRow();
            }
            $row->setFromArray($rowData);
            $row->save();
        }
        
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return true;
    }
    
    /**
     * Insert layout_to_page assignment
     * 
     * @param string $layout
     * @param string $page
     * @param int $priority
     * @param int $templateId
     * @return Axis_Core_Model_Template_Layout_Page Provides fluent interface
     */
    public function add($layout, $page, $priority = 100, $templateId = null)
    {
        if (null === $templateId) {
            $templateId = isset(Axis::config()->design->main->frontTemplateId) ? 
                Axis::config()->design->main->frontTemplateId : 1;
        }
        $page_id = Axis::single('core/page')->getPageIdByRequest($page);
        if (!$page_id) {
            $request = explode('/', $page);
            $page_id = Axis::single('core/page')->insert(array(
                'module_name' => $request[0],
                'controller_name' => $request[1],
                'action_name' => $request[2]
            ));
        }
        $this->insert(array(
            'template_id' => $templateId,
            'page_id' => $page_id,
            'layout' => $layout,
            'priority' => $priority
        ));
        return $this;
    }
    
    /**
     * Removes layout_to_page assignments
     * 
     * @param string $layout
     * @param string $page
     * @param int $templateId
     * @return Axis_Core_Model_Template_Layout_Page Provides fluent interface
     */
    public function remove($layout, $page, $templateId = null)
    {
        if (null === $templateId 
            && !$templateId = Axis::config()->design->main->frontTemplateId) {

            $templateId = 1;
        }
        $pageId = Axis::single('core/page')->getPageIdByRequest($page);
        if (!$pageId) {
            return $this;
        }
        $this->delete(
            "template_id = {$templateId} AND page_id = {$pageId} AND layout = '{$layout}'"
        );
        return $this;
    }
}