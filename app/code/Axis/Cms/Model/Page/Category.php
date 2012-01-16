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
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Model_Page_Category extends Axis_Db_Table 
{
    protected $_name = 'cms_page_category';
    protected $_primary = array('cms_category_id', 'cms_page_id');
    
    /**
     * Deletes existing rows.
     *
     * @param  array  id cms_categorty
     * @param  int siteId 
     * @return int          The number of rows deleted.
     */
    public function deleteBySiteId($where, $siteId)
    {
        $categories = Axis::model('cms/category')
            ->select('id')
            ->where('cc.site_id = ?', $siteId)
            ->fetchCol()
            ;
        $where .= $this->getAdapter()->quoteInto(
            "AND cms_category_id = ANY(?)", $categories
        );
        
        return $this->delete($where);     
    }
}