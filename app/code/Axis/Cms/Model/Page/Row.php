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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Model_Page_Row extends Axis_Db_Table_Row 
{
    /**
     *
     * @return Axis_Cms_Model_Page_Content_Row
     */
    public function getContent()
    {
        $languageId = Axis_Locale::getLanguageId();
        
        $columns = array(
            'title',  'content', 'meta_keyword', 'meta_description', 'meta_title'
        );
        $row = Axis::model('cms/page_content')
            ->select($columns)
            ->joinLeft('cms_page', 'cp.id = cpc.cms_page_id', array('layout'))
            ->where('cpc.language_id = ?', $languageId)
            ->where('cpc.cms_page_id = ?', $this->id)
            ->fetchRow()
        ;
        if (!$row) {
            return false;
        }
        $row->content = $row->getContent();
        return $row;
    }
    
    public function getComments()
    {
        return Axis::model('cms/page_comment')->select()
            ->where('cpc.cms_page_id = ?', $this->id)
            ->where('cpc.status = 1') //is approved
//            ->where('cpc.status <> 2') //not blocked
            ->order('cpc.created_on DESC')
            ->fetchAll()
            ;
   }
}