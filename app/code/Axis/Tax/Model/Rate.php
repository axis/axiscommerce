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
 * @package     Axis_Tax
 * @subpackage  Axis_Tax_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * TaxRateModel
 *
 * @category    Axis
 * @package     Axis_Tax
 * @subpackage  Axis_Tax_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
 class Axis_Tax_Model_Rate extends Axis_Db_Table
{
	/**
	 * The default table name 
	 */
	protected $_name = 'tax_rate';
	
    public function update(array $data, $where)
    {
        if (empty($data['modified_on'])) {
            $data['modified_on'] = Axis_Date::now()->toSQLString();
        }
        return parent::update($data, $where);
    }
    
    public function insert(array $data)
    {
        if (empty($data['created_on'])) {
            $data['created_on'] = Axis_Date::now()->toSQLString();
        }
        return parent::insert($data);
    }
    
    /**
     * Multi rows save  
     * @param array rows
     * @return bool
     */
    public function save($rows)
    {
        // Saving exists values
        foreach ($rows as $id => $row) {
            
            if (isset($row['id'])) {// update
                $this->update(
                    array(
                        'tax_class_id'      => $row['tax_class_id'],
                        'geozone_id'        => $row['geozone_id'],
                        'customer_group_id' => $row['customer_group_id'],
                        'rate'              => $row['rate'],
                        'description'       => $row['description']
                    ), 
                    $this->getAdapter()->quoteInto('id = ?', $id)
                );
            } else { // insert
                $isDuplicate = $this->select('id')
                    ->where('tax_class_id = ?', $row['tax_class_id'])
                    ->where('geozone_id = ?', $row['geozone_id'])
                    ->where('customer_group_id = ?', $row['customer_group_id'])
                    ->fetchOne();

                if ($isDuplicate) {
                    Axis::message()->addError(
                        Axis::translate('checkout')->__(
                            'Duplicate entry (must be unique set taxclass, geozone, customer group)'
                    ));
                    return false;
                }
                $this->insert(array(
                    'tax_class_id'      => $row['tax_class_id'],
                    'geozone_id'        => $row['geozone_id'],
                    'customer_group_id' => $row['customer_group_id'],
                    'rate'              => $row['rate'],
                    'description'       => $row['description']
                ));
            }
        }
        return true;
    }
    
    /**
     * Calculate Tax by Shopping Cart Id
     * 
     * @param int $cartId
     * @param array $geozoneIds
     * @return float
     */
    public function calculateByCartId($cartId, $geozoneIds, $customerGroupId)
    {

        $rawRows = Axis::single('checkout/cart_product')
            ->select(array('final_price', 'quantity'))
            ->join('catalog_product', 'cp.id = ccp.product_id', 'id')
            ->join('tax_rate', 'tr.tax_class_id = cp.tax_class_id', 'rate')
            ->join('location_geozone', 'lg.id = tr.geozone_id', 'priority')
            ->where('ccp.shopping_cart_id = ?', $cartId)
            ->where('tr.geozone_id IN (?)', $geozoneIds)
            ->where('tr.customer_group_id = ?', $customerGroupId)
            ->fetchAll()
            ;
        $rates = array();
        foreach ($rawRows as $row) {
            if (!isset($rates[$row['id']])
                || $rates[$row['id']]['priority'] < $row['priority']) {
            
                $rates[$row['id']] = $row['rate'];
            }
        }
        $tax = 0;
        foreach ($rawRows as $row) {
            $tax += $row['quantity'] * $row['final_price'] * $rates[$row['id']] / 100;
        }
        return round($tax, 2);
    }
    
    /**
     * Calculate tax by one price 
     *  
     * @param int price 
     * @param int $taxClassId
     * @param array $geozoneIds
     * @param int $customerGroupId
     * @return float
     */
    public function calculateByPrice(
        $price, $taxClassId, array $geozoneIds, $customerGroupId)
    {
        if (null == $taxClassId) {
            return 0;
        }
        $rate = (float) $this->select('rate')
            ->joinInner('location_geozone', 'lg.id = tr.geozone_id')
            ->where('tr.geozone_id IN (?)', $geozoneIds)
            ->where('tr.customer_group_id = ?', $customerGroupId)
            ->where('tr.tax_class_id = ?', $taxClassId)
            ->order('lg.priority DESC')
            ->limit(1)
            ->fetchOne()
            ;
        
        return round(($price * $rate) / 100, 2);
    }
}
