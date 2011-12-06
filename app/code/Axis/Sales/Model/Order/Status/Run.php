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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Status_Run
{

    /**
     *
     * @param string $method
     * @param Axis_Sales_Model_Order_Row $order
     * @return mixed bool
     */
    protected function _paymentCallback($method, $order)
    {
        return Axis_Payment::getMethod($order->payment_method_code)->$method($order);
    }
    /* @TODO Tracking, etc*/
//    protected function _shippingCallback($method, $order)
//    {
//        return Axis_Shipping::getMethod($order->shipping_method_code)->$method($order);
//    }

    /**
     *  "new" => "pending"
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function pending(Axis_Sales_Model_Order_Row $order)
    {
        if (!$this->_paymentCallback(__FUNCTION__, $order)) {
            return false;
        }
        $modelStock = Axis::single('catalog/product_stock');
        $products = $order->getProducts();
        // check the availability of all products
        foreach ($products as $product) {
            if ($product['backorder']) {
                continue;
            }

            if (!$modelStock->find($product['product_id'])->current()->canPending(
                $product['quantity'],
                $product['variation_id']
            )) {

                Axis::message()->addError(
                    Axis::translate('sales')->__(
                        "Can't buy %s with qty %s",
                        $product['name'],
                        $product['quantity']
                ));
                return false;
            }
        }

        // update stock data
        foreach ($products as $product) {
            if ($product['backorder']) {
                continue;
            }
            $stockRow = $modelStock->find($product['product_id'])->current();
            $quantity = $stockRow->getQuantity(
                $product['variation_id']
            );
            $stockRow->setQuantity(
                $quantity - $product['quantity'],
                $product['variation_id']
            );
        }

        return true;
    }

    /**
     *  "pending" => "processing"
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function processing(Axis_Sales_Model_Order_Row $order)
    {
        if (!$this->_paymentCallback(__FUNCTION__, $order)) {
            return false;
        }
        return true;
    }

    /**
     * "processing" => "ship"
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function ship(Axis_Sales_Model_Order_Row $order)
    {
        $modelStock = Axis::single('catalog/product_stock');
        $products = $order->getProducts();
        // check the availability of all products
        foreach ($products as $product) {
            if (!$product['backorder']) {
                continue;
            }
            if (!$modelStock->find($product['product_id'])->current()->canShipping(
                $product['quantity'],
                $product['variation_id']
            )) {
                 Axis::message()->addError(
                    Axis::translate('sales')->__(
                        "Product can't be shipped. Name: %s, Sku: %s",
                        $product['name'],
                        $product['sku']
                    )
                 );
                return false;
            }
        }
        // update stock data for backordered products
        // normal products update the stock in pending status
        foreach ($products as $product) {
            if (!$product['backorder']) {
                continue;
            }
            $stockRow = $modelStock->find($product['product_id'])->current();
            $quantity = $stockRow->getQuantity(
                $product['variation_id']
            );
            $stockRow->setQuantity(
                $quantity - $product['quantity'],
                $product['variation_id']
            );
        }
        return true;
    }

    public function delivered(Axis_Sales_Model_Order_Row $order)
    {
        // @todo _shippingCallback
        return true;
    }

    public function complete(Axis_Sales_Model_Order_Row $order)
    {
        return true;
    }

    public function hold(Axis_Sales_Model_Order_Row $order)
    {
        return true;
    }

    public function cancel(Axis_Sales_Model_Order_Row $order)
    {
        if (!$this->_paymentCallback(__FUNCTION__, $order)) {
            return false;
        }
        $modelStock = Axis::single('catalog/product_stock');
        foreach ($order->getProducts() as $product) {
            $stockRow = $modelStock->find($product['product_id'])->current();
            $quantity = $stockRow->getQuantity($product['variation_id']);
            $stockRow->setQuantity(
                $quantity + $product['quantity'],
                $product['variation_id']
            );
        }
        return true;
    }

    public function refund(Axis_Sales_Model_Order_Row $order)
    {
        if (!$this->_paymentCallback(__FUNCTION__, $order)) {
            return false;
        }
        $modelStock = Axis::single('catalog/product_stock');
        foreach ($order->getProducts() as $product) {
            $stockRow = $modelStock->find($product['product_id'])->current();
            $quantity = $stockRow->getQuantity($product['variation_id']);
            $stockRow->setQuantity(
                $quantity + $product['quantity'],
                $product['variation_id']
            );
        }
        return true;
    }

    /**
     * Must always return true
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function failed(Axis_Sales_Model_Order_Row $order)
    {
        $this->_paymentCallback(__FUNCTION__, $order);

        $modelStock = Axis::single('catalog/product_stock');
        foreach ($order->getProducts() as $product) {
            $stockRow = $modelStock->find($product['product_id'])->current();
            $quantity = $stockRow->getQuantity($product['variation_id']);
            $stockRow->setQuantity(
                $quantity + $product['quantity'],
                $product['variation_id']
            );
        }
        return true;
    }

    public function __call($call, $argv)
    {
        return in_array($call, Axis_Sales_Model_Order_Status::collect());
    }
}
