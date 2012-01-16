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
class Axis_Sales_Model_Order extends Axis_Db_Table
{
    protected $_name = 'sales_order';

    protected $_rowClass = 'Axis_Sales_Model_Order_Row';

    protected $_selectClass = 'Axis_Sales_Model_Order_Select';

    /**
     * Create order, and clears shopping cart after that
     *
     * @return Axis_Sales_Model_Order_Row
     * @throws Axis_Exception
     */
    public function createFromCheckout()
    {
        /**
         * @var Axis_Checkout_Model_Checkout  $checkout
         */
        $checkout = Axis::single('checkout/checkout');

        if (!$checkout->getCart()->validateContent()) {
            throw new Axis_Exception();
        }

        $orderRow = $this->createRow();
        $storage = $checkout->getStorage();

        if ($customer = Axis::getCustomer()) {
            $orderRow->customer_id = $customer->id;
            $orderRow->customer_email = $customer->email;
        } else {
            $orderRow->customer_email = $checkout->getBilling()->email;
        }

        $orderRow->site_id = Axis::getSiteId();

        $orderRow->payment_method = $checkout->payment()->getTitle();
        $orderRow->payment_method_code = $checkout->payment()->getCode();

        $orderRow->shipping_method = $checkout->shipping()->getTitle();
        $orderRow->shipping_method_code = $checkout->shipping()->getCode();

        $orderRow->date_purchased_on = Axis_Date::now()->toSQLString();
        $orderRow->currency = Axis::single('locale/currency')->getCode();
        $orderRow->currency_rate = Axis::single('locale/currency')->getData(
            $orderRow->currency, 'rate'
        );
        $orderRow->order_total = $checkout->getTotal()->getTotal();
        $orderRow->txn_id = 0; //@todo
        $orderRow->order_status_id = 0;
        $orderRow->ip_address = $_SERVER['REMOTE_ADDR'];
        $orderRow->customer_comment = $storage->customer_comment;
        $orderRow->locale = Axis_Locale::getLocale()->toString();

        /* build delivery & billing arrays */
        $addressFormatId = Axis::config('core/store/addressFormat');

        $delivery = $checkout->getDelivery()->toArray();
        $delivery['country'] = isset($delivery['country']['name']) ?
            $delivery['country']['name'] : '';

        if (isset($delivery['zone']['name'])) {
            $delivery['state'] = $delivery['zone']['name'];
        }
        $delivery['address_format_id'] = $addressFormatId;

        $billing = $checkout->getBilling()->toArray();
        $billing['country'] = isset($billing['country']['name'])
            ? $billing['country']['name'] : '';
        if (isset($billing['zone']['name'])) {
            $billing['state'] = $billing['zone']['name'];
        }
        $billing['address_format_id'] = $addressFormatId;

        unset($billing['id'], $delivery['id']);

        foreach ($delivery  as $key => $value) {
            $delivery['delivery_' . $key] = $value;
        }
        foreach ($billing  as $key => $value) {
            $billing['billing_' . $key] = $value;
        }

        $orderRow->setFromArray($delivery);
        $orderRow->setFromArray($billing);

        /* Save order (auto generate number ) */
        $orderRow->save();

        /* Add products to order and change quantity */
        $modelOrderProduct = Axis::single('sales/order_product');
        foreach ($checkout->getCart()->getProducts() as $product) {
            $modelOrderProduct->add($product, $orderRow->id);
        }

        /* Add total info */
        $total = $checkout->getTotal();
        $orderTotal = Axis::single('sales/order_total');
        foreach ($total->getCollects() as $collect) {
            $orderTotal->insert(array(
                'order_id' => $orderRow->id,
                'code'     => $collect['code'],
                'title'    => $collect['title'],
                'value'    => $collect['total']
            ));
        }

        // update product stock
        $orderRow->setStatus('pending');

        return $orderRow;
    }

    /**
     *
     * @param int $orderId
     * @return array
     */
    public function getProducts($orderId)
    {
        $products = Axis::single('sales/order_product')
            ->select('*')
            ->where('order_id = ?', $orderId)
            ->joinLeft('catalog_product_stock',
                'sop.product_id = cps.product_id',
                'decimal'
            )
            ->fetchAssoc();

        /* select attributes */
        $attributes = Axis::single('sales/order_product_attribute')
            ->select('*')
            ->join('sales_order_product', 'sop.id = sopa.order_product_id')
            ->where('sop.order_id = ?', $orderId)
            ->fetchAll()
            ;
        foreach ($attributes as $attribute) {
            $products[$attribute['order_product_id']]['attributes'][] = $attribute;
        }

        return $products;
    }

    /**
     *
     * @param   int     $orderId
     * @return  float
     */
    public function getShipping($orderId)
    {
        return Axis::single('sales/order_total')->getShipping($orderId);
    }

    /**
     *
     * @param   int     $orderId
     * @return  float
     */
    public function getTax($orderId)
    {
        return Axis::single('sales/order_total')->getTax($orderId);
    }

    /**
     *
     * @param   int     $orderId
     * @return  float
     */
    public function getShippingTax($orderId)
    {
        return Axis::single('sales/order_total')->getShippingTax($orderId);
    }

    /**
     *
     * @param   int     $orderId
     * @return  float
     */
    public function getSubtotal($orderId)
    {
        return Axis::single('sales/order_total')->getSubtotal($orderId);
    }

    /**
     *
     * @param int $customerId
     * @param int $siteId
     * @return int
     */
    public function getOrdersByCustomer($customerId, $siteId = null)
    {
        if (null === $siteId) {
            $siteId = Axis::getSiteId();
        }
        $orderStatuses = array();

        $orderStatusRowset = Axis::single('sales/order_status_text')->getList();
        foreach ($orderStatusRowset as $orderStatus) {
            $orderStatuses[$orderStatus['status_id']] = $orderStatus;
        }

        $orders = $this->fetchAll(array(
            $this->getAdapter()->quoteInto('customer_id = ?', $customerId),
            $this->getAdapter()->quoteInto('site_id = ?', $siteId)
        ), 'date_purchased_on DESC')->toArray();

        foreach ($orders as &$order) {

            $order['order_total'] = Axis::single('locale/currency')->from(
                $order['order_total'] * $order['currency_rate'],
                $order['currency']
            );

            $order['order_status_name'] = isset(
                $orderStatuses[$order['order_status_id']]) ?
                    $orderStatuses[$order['order_status_id']]['status_name'] :
                    Axis::translate('sales')->__('Undefined');
        }
        return $orders;
    }
}