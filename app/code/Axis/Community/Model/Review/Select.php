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
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Model_Review_Select extends Axis_Db_Table_Select
{
    /**
     * @return  Axis_Admin_Model_Cms_Page_Select Fluent interface
     */
    public function addRating()
    {
       $this->joinLeft('community_review_mark',
            'cr.id = crm.review_id',
            array('rating' => new Zend_Db_Expr('AVG(crm.mark)'))
       )
       ->group('cr.id');
        return $this;
    }

    /**
     * @param integer $languageId [optional]
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addProductDescription($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        return $this->joinLeft('catalog_product_description',
            'cr.product_id = cpd.product_id AND cpd.language_id = ' . $languageId,
            array(
                'product_name'              => 'name',
                'product_image_seo_name'    => 'image_seo_name'
            )
        );
    }

    /**
     * Rewriting of parent method
     * Add having statement if filter by rating is required
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
            if ('rating' != $filter['field']) {
                continue;
            }
            $this->having("rating {$filter['operator']} ?", $filter['value']);
            unset($filters[$key]);
        }

        return parent::addFilters($filters);
    }
}