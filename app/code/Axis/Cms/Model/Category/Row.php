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
class Axis_Cms_Model_Category_Row extends Axis_Db_Table_Row  
{
    public function getContent($languageId = null)
    {
        if (null == $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        /*get category title & description*/
        $cols = array(
            'title',
            'description',
            'meta_keyword',
            'meta_description',
            'meta_title'
        );
        return Axis::single('cms/category_content')->select($cols)
            ->where('ccc.cms_category_id = ?', $this->id)
            ->where('ccc.language_id = ?', $languageId)
            ->fetchRow()
            ;
    }
  

    public function getChilds($languageId = null)
    {
        if (null == $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        /*get childs categories*/
        return Axis::single('cms/category_content')
            ->select(array('link', 'title'))
            ->joinInner('cms_category', 'ccc.cms_category_id = cc.id')
            ->where('cc.parent_id = ?', $this->id)
            ->where('cc.is_active = 1')
            ->where('ccc.language_id = ?', $languageId)
            ->where('ccc.link IS NOT NULL')
            ->fetchAll();
    }

    public function getPages($languageId = null)
    {
        if (null == $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        /*get pages*/
        return Axis::single('cms/page_content')->select(array('link', 'title'))
            ->joinInner(
                array('cpca' => 'cms_page_category'),
                'cpc.cms_page_id = cpca.cms_page_id'
            )
            ->joinInner(
                'cms_page',
                'cp.id = cpc.cms_page_id'
            )
            ->where('cpca.cms_category_id = ?', $this->id)
            ->where('cpc.language_id = ?', $languageId)
            ->where('cp.is_active = 1')
            ->where('cpc.link IS NOT NULL')
            ->fetchAll();
    }
}