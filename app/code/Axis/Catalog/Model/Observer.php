<?php
/**
 * Axis
 *
 * @copyright Copyright 2008-2010 Axis
 * @license GNU Public License V3.0
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
class Axis_Catalog_Model_Observer
{
    /**
     * Sends catalog notificatations
     * - catalog_product_in_stock
     * - catalog_product_backorder
     *
     * @param array $data
     * - old_data     = Stock array data before save
     * - stock      = Axis_Catalog_Model_Product_Stock_Row
     * - product    = Axis_Catalog_Model_Product_Row
     * @return void
     */
    public function notifyStockUpdate($data)
    {
        if (($data['old_data']['in_stock'] == 0 && $data['stock']->in_stock == 1)
            || ($data['old_data']['backorder'] == 0 && $data['stock']->backorder == 1)
                && $data['product']->quantity > $data['stock']->min_qty) {

            if ($data['stock']->in_stock) {
                Axis::dispatch('catalog_product_in_stock', $data);
            } else {
                Axis::dispatch('catalog_product_backorder', $data);
            }
        }
    }

    /**
     * Sends catalog notifications:
     * - catalog_product_backorder
     * - catalog_product_in_stock
     * - catalog_product_low_stock
     * - catalog_product_out_of_stock
     *
     * @param array $data
     * - old_quantity   = float
     * - new_quantity   = float
     * - product        = Axis_Catalog_Model_Product_Row
     * - variation      = Axis_Db_Table_Row[optional]
     * - stock          = Axis_Catalog_Model_Product_Stock_Row
     * @return void
     */
    public function notifyQuantityUpdate($data)
    {
        if (($data['old_quantity'] <= $data['stock']->min_qty
            && $data['new_quantity'] > $data['stock']->min_qty)
                && ($data['stock']->in_stock || $data['stock']->backorder)) {

            if ($data['stock']->in_stock) {
                Axis::dispatch('catalog_product_in_stock', $data);
            } else {
                Axis::dispatch('catalog_product_backorder', $data);
            }
        } elseif ($data['old_quantity'] > $data['stock']->notify_qty
            && $data['new_quantity'] <= $data['stock']->notify_qty) {

            Axis::dispatch('catalog_product_low_stock', $data);
        } elseif ($data['old_quantity'] > $data['stock']->min_qty
            && $data['new_quantity'] <= $data['stock']->min_qty) {

            Axis::dispatch('catalog_product_out_of_stock', $data);
        }
    }

    /**
     * Calls for notifyQuantityUpdate after saving existing product
     * Sends catalog notifications:
     * - catalog_product_backorder
     * - catalog_product_in_stock
     * - catalog_product_low_stock
     * - catalog_product_out_of_stock
     *
     * @param array $data
     * - old_data   = Product old data. Null if product is new
     * - product    = Axis_Catalog_Model_Product_Row
     */
    public function onProductUpdate($data)
    {
        if (null === $data['old_data']) {
            return;
        }

        $stock = Axis::model('catalog/product_stock')
            ->find($data['product']->id)
            ->current();

        $this->notifyQuantityUpdate(array(
            'old_quantity'  => $data['old_data']['quantity'],
            'new_quantity'  => $data['product']->quantity,
            'product'       => $data['product'],
            'stock'         => $stock
        ));
    }

    /**
     * Update catalog_product_price_index table after product save
     * Sends notifications:
     * - catalog_product_price_update
     *
     * @param array $data
     * - old_data   = Product old data. Null if product is new
     * - product    = Axis_Catalog_Model_Product_Row
     */
    public function updatePriceIndexOnProductSave($data)
    {
        Axis::model('catalog/product_price_index')->updateIndexesByProducts(
            array($data['product']['id'] => $data['product']->toArray())
        );
    }

    /**
     * Update price indexes after removing product from some categories
     *
     * @param array $data
     * - product_ids    = array
     * - category_ids   = array
     */
    public function updatePriceIndexOnProductRemove($data)
    {
        Axis::model('catalog/product_price_index')->updateIndexesByProducts(
            Axis::model('catalog/product')
                ->select('*')
                ->where('id IN (?)', $data['product_ids'])
                ->fetchAssoc()
        );
    }

    /**
     * Update price indexes after moving product between categories
     *
     * @param array $data
     * - product_ids    = array
     * - category_id    = int
     */
    public function updatePriceIndexOnProductMove($data)
    {
        Axis::model('catalog/product_price_index')->updateIndexesByProducts(
            Axis::model('catalog/product')
                ->select('*')
                ->where('id IN (?)', $data['product_ids'])
                ->fetchAssoc()
        );
    }

    /**
     * Update catalog_product_price_index table after discount save
     * Sends notifications:
     * - catalog_product_price_update
     *
     * @param array $data
     * - old_data   = Discount old data. Null if discount is new
     * - discount   = Axis_Discount_Model_Discount_Row
     */
    public function updatePriceIndexOnDiscountSave($data)
    {
        $oldProducts = array();
        if (null !== $data['old_data']) {
            if (!$data['old_data']['is_active']
                && !$data['discount']['is_active']) {

                return;
            }
            $oldProducts = $data['old_data']['products'];
        }

        Axis::model('catalog/product_price_index')->updateIndexesByProducts(
            $oldProducts + $data['discount']->getApplicableProducts()
        );
    }

    /**
     * Updates catalog_product_price_index after dicount was deleted
     *
     * @param array $data
     * - discount_data
     */
    public function updatePriceIndexOnDiscountDelete($data)
    {
        Axis::model('catalog/product_price_index')->updateIndexesByProducts(
            $data['discount_data']['products']
        );
    }

    /**
     * Updates catalog_product_price_index after customer group was added
     *
     * @param array $data
     * - old_data   = Customer group old data
     * - group      = Axis_Db_Table_Row
     */
    public function updatePriceIndexOnCustomerGroupAdd($data)
    {
        if (null !== $data['old_data']) {
            return;
        }

        Axis::model('catalog/product_price_index')->updateIndexesByCustomerGroupIds(
            array($data['group']['id'])
        );
    }
    
    public function catalogProductViewAddLogEvent($observer) 
    {
        $product = $observer['product'];
        if (!$product instanceof Axis_Catalog_Model_Product_Row){
            return;
        }
        $visitor = Axis::single('log/visitor')->getVisitor();
        Axis::model('log/event')->createRow(array(
            'visitor_id' => $visitor->id,
            'event_name' => 'catalog_product_view',
            'object_id'  => $product->id
        ))->save();
    }
}
