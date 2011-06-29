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
 * @copyright   Copyright 2008-2011 Axis
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
    protected $_name = 'tax_rate';

    public function update(array $data, $where)
    {
        unset($data['created_on']);
        $data['modified_on'] = Axis_Date::now()->toSQLString();
        return parent::update($data, $where);
    }

    public function insert(array $data)
    {
        $data['created_on'] = Axis_Date::now()->toSQLString();
        return parent::insert($data);
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
        $customerGroupId = array(
            $customerGroupId,
            Axis_Account_Model_Customer_Group::GROUP_ALL_ID
        );

        $rawRows = Axis::single('checkout/cart_product')
            ->select(array('final_price', 'quantity'))
            ->join('catalog_product', 'cp.id = ccp.product_id', 'id')
            ->join('tax_rate', 'tr.tax_class_id = cp.tax_class_id', 'rate')
            ->join('location_geozone', 'lg.id = tr.geozone_id', 'priority')
            ->where('ccp.shopping_cart_id = ?', $cartId)
            ->where('tr.geozone_id IN (?)', $geozoneIds)
            ->where('tr.customer_group_id IN(?)', $customerGroupId)
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
        $customerGroupId = array(
            $customerGroupId,
            Axis_Account_Model_Customer_Group::GROUP_ALL_ID
        );
        $rate = (float) $this->select('rate')
            ->joinInner('location_geozone', 'lg.id = tr.geozone_id')
            ->where('tr.geozone_id IN (?)', $geozoneIds)
            ->where('tr.customer_group_id IN (?)', $customerGroupId) // 0 - all customers
            ->where('tr.tax_class_id = ?', $taxClassId)
            ->order('lg.priority DESC')
            ->limit(1)
            ->fetchOne()
            ;

        return round(($price * $rate) / 100, 2);
    }
}
