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
class Axis_Catalog_Model_Product_Variation extends Axis_Db_Table
{
    protected $_name = 'catalog_product_variation';
    protected $_dependentTables = array('Axis_Catalog_Model_Product_Attribute');
    protected $_referenceMap = array(
        'Product' => array(
            'columns'           => 'product_id',
            'refTableClass'     => 'Axis_Catalog_Model_Product',
            'refColumns'        => 'id'
        )
    );
    
    public function insert(array $data)
    {
        if (empty($data['weight'])) {
            $data['weight'] = 0;
        }
        if (empty($data['price'])) {
            $data['price'] = 0;
        }
        return parent::insert($data);
    }

    public function getVariationsByProductIds(array $productIds)
    {
        $rowset = $this->fetchAll(
            $this->getAdapter()->quoteInto('product_id IN (?) OR product_id IS NULL', $productIds)
        );
        $result = array();
        foreach ($productIds as $productId) {
            foreach ($rowset as $row) {
                if ($productId == $row->product_id) {
                    $result[$row->product_id][$row->id] = $row->toArray();
                }
            }
        }
        return $result;
    }
}