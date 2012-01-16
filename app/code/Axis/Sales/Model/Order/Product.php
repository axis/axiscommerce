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
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Product extends Axis_Db_Table
{
    protected $_name = 'sales_order_product';

    /**
     * Add product to order
     *
     * @param array $product
     * @param int $orderId
     * @return int orderProductId
     */
    public function add($product, $orderId)
    {
        $stock = Axis::single('catalog/product_stock')
            ->find($product['product_id'])
            ->current();
        $backorder = $stock->backorder;
        $productVariationId = isset($product['variation_id']) ?
            $product['variation_id'] : null;

        $stockQuantity = $stock->getQuantity($productVariationId);
        if ($stockQuantity - $product['quantity'] > $stock->min_qty) {
            $backorder = 0;
        }

        //calculate tax
        $taxClassId = Axis::single('catalog/product')->getTaxClassId(
            $product['product_id']
        );
        $orderRow = Axis::single('sales/order')
            ->find($orderId)
            ->current();

        $countryId = Axis::single('location/country')->getIdByName(
            $orderRow->delivery_country
        );
        $zoneId = 0;
        if (!empty($orderRow->delivery_state)) {
            $zoneId = Axis::single('location/zone')->getIdByName(
                $orderRow->delivery_state
            );
        }
        $geozoneIds = Axis::single('location/geozone')
            ->getIds($countryId, $zoneId);
        $customerGroupId = Axis::single('account/customer')
            ->getGroupId($orderRow->customer_id);

        $productTax = Axis::single('tax/rate')->calculateByPrice(
            $product['final_price'], $taxClassId, $geozoneIds, $customerGroupId
        );

        $orderProductId = $this->insert(array(
            'order_id'             => $orderId,
            'product_id'           => $product['product_id'],
            'variation_id'         => $productVariationId,
            'sku'                  => $product['sku'],
            'name'                 => $product['name'],
            'price'                => $product['price'],
            'tax'                  => $productTax,
            'final_price'          => $product['final_price'],
            'final_weight'         => $product['final_weight'],
            'quantity'             => $product['quantity'],
            'backorder'            => $backorder
        ));

       // $orderProductId = $this->getAdapter()->lastInsertId();
        if (isset($product['attributes']) && is_array($product['attributes'])) {
            $modelAttributte = Axis::single('sales/order_product_attribute');
            foreach ($product['attributes'] as $attribute) {
                $modelAttributte->insert(array(
                    'order_product_id'     => $orderProductId,
                    'product_option'       => $attribute['product_option'],
                    'product_option_value' => $attribute['product_option_value'],
                ));
            }
        }

        $productRow = Axis::single('catalog/product')
            ->find($product['product_id'])
            ->current();
        $productRow->ordered += 1;//$product['quantity'];
        $productRow->save();

        return $orderProductId;
    }
}