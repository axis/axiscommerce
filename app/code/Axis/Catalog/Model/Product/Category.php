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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Category extends Axis_Db_Table
{
    protected $_name = 'catalog_product_category';
    protected $_selectClass = 'Axis_Catalog_Model_Product_Category_Select';


    /**
     * Retrieve the categories ids for the recieved product ids
     *
     * @return array
     * <pre>
     * array(
     *  product_id => array(
     *      category_id, ...
     *  )
     * )
     * </pre>
     */
    public function getCategoriesByProductIds(array $productIds)
    {
        $categories = array();
        $rowset = $this->select()
            ->where('product_id IN (?)', $productIds)
            ->fetchAll();

        foreach ($rowset as $row) {
            $categories[$row['product_id']][] = $row['category_id'];
        }
        return $categories;
    }

    /**
     * Retrieve site ids array. Keys are the product id
     *
     * @param array $productIds
     * @return array
     * <pre>
     * array(
     *  product_id => array(
     *      site_id, ...
     *  )
     * )
     * </pre>
     */
    public function getSitesByProductIds(array $productIds)
    {
        $sites = array();
        $rowset = $this->select('*')
            ->joinInner(
                'catalog_category',
                'cc.id = cpc.category_id',
                'cc.site_id'
            )
            ->where('product_id IN (?)', $productIds)
            ->fetchAll();

        foreach ($rowset as $row) {
            $sites[$row['product_id']][$row['site_id']] = $row['site_id'];
        }
        return $sites;
    }

    /**
     * Retrieve the category list
     * with category description for one product
     *
     * @param int $productId
     * @param int $languageId
     * @return array Where the keys are the cateogory id
     */
    public function getCategoriesByProductId($productId, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        return $this->select('*')
            ->joinLeft(
                'catalog_category_description',
                'ccd.category_id = cpc.category_id',
                '*'
            )
            ->where('cpc.product_id = ?', $productId)
            ->where('ccd.language_id = ?', $languageId)
            ->fetchAssoc();
    }
}