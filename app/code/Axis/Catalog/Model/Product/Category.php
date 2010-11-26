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

    public function getCategoriesByProductIds(array $productIds)
    {
        $categories = array();
        $rowset = $this->select()
            ->where('product_id IN(?)', $productIds)
            ->fetchAll();
        foreach ($rowset as $row) {
            $categories[$row['product_id']][] = $row['category_id'];
        }

        return $categories;
    }

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