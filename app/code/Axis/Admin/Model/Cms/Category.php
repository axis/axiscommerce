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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Cms_Category extends Axis_Cms_Model_Category
{
    public function getRootCategory($siteId)
    {
        $select = $this->getAdapter()->select();
        $select->from(
                array('cc' => $this->_prefix . 'cms_category'),
                array('id', 'name', 'is_active')
            )
            ->where('cc.site_id = ?', $siteId)
            ->where('cc.parent_id is NULL');

        return $this->getAdapter()->fetchAssoc($select->__toString());
    }
    
    public function getChildCategory($nodeId)
    {
        $select = $this->getAdapter()->select();
        $select->from(
                array('cc' => $this->_prefix . 'cms_category'),
                array('id', 'name', 'is_active')
            )
            ->where('cc.parent_id = ?', $nodeId);

        return $this->getAdapter()->fetchAssoc($select->__toString());
    }
}