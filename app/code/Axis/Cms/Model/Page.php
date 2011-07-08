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
class Axis_Cms_Model_Page extends Axis_Db_Table
{
    protected $_name = 'cms_page';

    protected $_rowClass = 'Axis_Cms_Model_Page_Row';

    protected $_selectClass = 'Axis_Cms_Model_Page_Select';

    /**
     * Update or insert page row
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        if (!$data['id']) {
            unset($data['id']);
        }
        $row = $this->getRow($data);
        $row->save();
        return $row;
    }

    public function getPageIdByLink($link)
    {
        return Axis::single('cms/page_content')->select('cms_page_id')
            ->joinInner(
                array('cpca' => 'cms_page_category'),
                'cpc.cms_page_id = cpca.cms_page_id'
            )
            ->joinInner(
                'cms_category',
                'cc.id = cpca.cms_category_id'
            )
            ->where('cpc.link = ?', $link)
            ->where('cc.site_id = ?', Axis::getSiteId())
            ->fetchOne();
    }

    /**
     *  return root cms pages
     *
     */
    public function getBoxPages()
    {
        $languageId = Axis_Locale::getLanguageId();
        return $this->select('id')
            ->distinct()
            ->joinLeft(
                'cms_page_content',
                'cpc.cms_page_id = cp.id',
                array('link', 'title')
            )
            ->joinLeft(
                array('cptc' => 'cms_page_category'),
                'cptc.cms_page_id = cp.id'
            )
//            ->joinLeft(
//                'cms_category_content',
//                'ccc.cms_category_id = cptc.cms_category_id'
//            )
            ->joinLeft(
                'cms_category',
                'cptc.cms_category_id = cc.id'
            )
            ->where('cp.show_in_box = 1')
            ->where('cp.is_active = 1')
            ->where('cc.is_active = 1')
            ->where('cpc.language_id = ?' , $languageId)
            ->where('cpc.link IS NOT NULL')
            ->fetchAll()
            ;
    }
}