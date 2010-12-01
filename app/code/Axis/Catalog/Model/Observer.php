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
     * Send in_stock notificatations
     *
     * @param array $data
     *  Axis_Catalog_Model_Product_Row|Axis_Catalog_Model_Product_Variation_Row $product
     *  Axis_Catalog_Model_Product_Stock_Row $stock
     * @return void
     */
    public function notifyStockUpdate($data)
    {
        if (($data['old_in_stock'] == 0 && $data['stock']->in_stock == 1)
            || ($data['old_backorder'] == 0 && $data['stock']->backorder == 1)
                && $data['product']->quantity > $data['stock']->min_qty) {

            if ($data['stock']->in_stock) {
                Axis::dispatch('catalog_product_in_stock', $data);
            } else {
                Axis::dispatch('catalog_product_backorder', $data);
            }
        }
    }

    /**
     * Send low_stock, in_stock, out_of_stock notificatations
     *
     * @param array $data
     *  Axis_Catalog_Model_Product_Row|Axis_Catalog_Model_Product_Variation_Row $product
     *  Axis_Catalog_Model_Product_Stock_Row $stock
     * @return void
     */
    public function notifyQuantityUpdate($data)
    {
        if (($data['old_quantity'] <= $data['stock']->min_qty
            && $data['product']->quantity > $data['stock']->min_qty)
                && ($data['stock']->in_stock || $data['stock']->backorder)) {

            if ($data['stock']->in_stock) {
                Axis::dispatch('catalog_product_in_stock', $data);
            } else {
                Axis::dispatch('catalog_product_backorder', $data);
            }
        } elseif ($data['old_quantity'] > $data['stock']->notify_qty
            && $data['product']->quantity <= $data['stock']->notify_qty) {

            Axis::dispatch('catalog_product_low_stock', $data);
        } elseif ($data['old_quantity'] > $data['stock']->min_qty
            && $data['product']->quantity <= $data['stock']->min_qty) {

            Axis::dispatch('catalog_product_out_of_stock', $data);
        }
    }
}