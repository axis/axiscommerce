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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Cms_Page_Select extends Axis_Db_Table_Select
{
    /**
     *
     * @param int $languageId [optional]
     * @return  Axis_Admin_Model_Cms_Page_Select Fluent interface
     */
    public function addContent($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        $this->joinLeft(
            'cms_page_content',
            $this->getAdapter()->quoteInto(
                'cp.id = cpc.cms_page_id AND cpc.language_id = ?', $languageId
            ),
            '*'
        );
        return $this;
    }

    /**
     *
     * @return  Axis_Admin_Model_Cms_Page_Select Fluent interface
     */
    public function addCategoryName()
    {
        $this->joinLeft(
                array('cpc2'=> 'cms_page_category'),
                'cpc2.cms_page_id = cp.id'
            )
            ->joinLeft(
                'cms_category',
                'cc.id = cpc2.cms_category_id',
                array('category_name' =>
                    new Zend_Db_Expr('GROUP_CONCAT(cc.name separator \', \')')
                )
            );
        
        return $this;
    }

    /**
     * @param int $siteId
     * @return  Axis_Admin_Model_Cms_Page_Select Fluent interface
     */
    public function addSiteFilter($siteId)
    {
        //cc cms_category
        $this->where('cc.site_id = ?', $siteId);
        return $this;
    }

    /**
     * @param string|array $categories
     * @return  Axis_Admin_Model_Cms_Page_Select Fluent interface
     */
    public function addCategoriesFilter($categories = 'all')
    {
        if ('lost' === $categories) {
            $this->addLostFilter();
        } else if ('all' != $categories) {

            if (!is_array($categories)) {
                $categories = array($categories);
            }
            $this->where('cpc2.cms_category_id IN (?)', $categories);
        }

        return $this;
    }


    /**
     * @return  Axis_Admin_Model_Cms_Page_Select Fluent interface
     */
    public function addLostFilter()
    {
            $subSelect = Axis::single('cms/page_category')
                ->select('cms_page_id')
                ->distinct();

            $this->where("cp.id <> ALL (?)", $subSelect);

        return $this;
    }
}