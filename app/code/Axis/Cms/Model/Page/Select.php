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
class Axis_Cms_Model_Page_Select extends Axis_Db_Table_Select
{
    /**
     * Add all columns from cms_page_content table to select
     *
     * @param int $languageId [optional]
     * @return  Axis_Admin_Model_Cms_Page_Select
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
     * Adds all names of categories where the page lies in, devided by commas
     *
     * @return Axis_Admin_Model_Cms_Page_Select
     */
    public function addCategoryName()
    {
        $this->group('cp.id')
            ->joinLeft(
                'cms_page_category',
                'cpc.cms_page_id = cp.id'
            )
            ->joinLeft(
                'cms_category',
                'cc.id = cpc.cms_category_id',
                array(
                    'category_name' =>
                        new Zend_Db_Expr('GROUP_CONCAT(cc.name separator \', \')')
                )
            );

        return $this;
    }


    /**
     * Adds filter to get only uncategorized pages
     *
     * @return Axis_Admin_Model_Cms_Page_Select
     */
    public function addFilterByUncategorized()
    {
        $subSelect = Axis::model('cms/page_category')
            ->select('cms_page_id')
            ->distinct();

        $this->where("cp.id <> ALL (?)", $subSelect);

        return $this;
    }

    /**
     * Rewriting of parent method
     * Add having statement if filter by category_name is required
     *
     * @param array $filters
     * <pre>
     *  array(
     *      0 => array(
     *          field       => table_column
     *          value       => column_value
     *          operator    => =|>|<|IN|LIKE    [optional]
     *          table       => table_correlation[optional]
     *      )
     *  )
     * </pre>
     * @return Axis_Db_Table_Select
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $key => $filter) {
            if ('category_name' != $filter['field']) {
                continue;
            }
            $this->having("category_name LIKE ?",  '%' . $filter['value'] . '%');
            unset($filters[$key]);
        }

        return parent::addFilters($filters);
    }
}