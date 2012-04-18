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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Template_Page extends Axis_Db_Table
{
    protected $_name = 'core_template_page';
    
    /**
     * Insert layout to page assignment
     * 
     * @param string $layout
     * @param string $page
     * @param int $templateId
     * @param string $parentPage
     * @param int $priority
     * @return Axis_Core_Model_Template_Page Provides fluent interface
     */
    public function add($layout, $page, $templateId = null, $parentPage = null, $priority = 100)
    {
        $pageId = Axis::single('core/page')->add($page)->getIdByPage($page);
        if (null === $templateId) {
            $templateId = Axis::config('design/main/frontTemplateId');
        }
        if (!empty($parentPage)) {
            $parentPage = Axis::single('core/page')->getIdByPage($parentPage);
        }

        $this->insert(array(
            'template_id'    => $templateId,
            'page_id'        => $pageId,
            'layout'         => $layout,
            'parent_page_id' => $parentPage,
            'priority'       => $priority
        ));
        return $this;
    }
    
    /**
     * Removes layout to page assignments
     * 
     * @param string $layout
     * @param string $page
     * @param int $templateId
     * @return Axis_Core_Model_Template_Page Provides fluent interface
     */
    public function remove($layout, $page, $templateId = null)
    {
        if (null === $templateId) {

            $templateId = Axis::config('design/main/frontTemplateId');
        }
        $pageId = Axis::single('core/page')->getIdByPage($page);
        if (!$pageId) {
            return $this;
        }
        $this->delete(
            "template_id = {$templateId} AND page_id = {$pageId} AND layout = '{$layout}'"
        );
        return $this;
    }

    /**
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        $row->save();
        return $row;
    }
}