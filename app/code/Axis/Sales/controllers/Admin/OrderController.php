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
 * @subpackage  Axis_Sales_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Admin_OrderController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('sales')->__('Orders');
        $this->view->orderStatuses = array_values(
            Axis::single('sales/order_status_text')->getList()
        );
        if ($this->_hasParam('orderId')) {
            $this->view->orderId = $this->_getParam('orderId');
        }
        $this->render();
    }

    public function listAction()
    {
        $select = Axis::single('sales/order')->select('*')
            ->columns(array(
                'order_total_customer' => new Zend_Db_Expr('currency_rate * order_total')
            ))
            ->calcFoundRows()
            ->joinLeft('account_customer',
                'so.customer_id = ac.id',
                array('customer_name' => new Zend_Db_Expr('CONCAT(firstname, " ", lastname)'))
            )
            ->addFilters($this->_getParam('filter', array()))
            ->limit($this->_getParam('limit', 25), $this->_getParam('start', 0))
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        $data = $select->fetchAll();
        $modelCurrency = Axis::single('locale/currency');
        $mainStoreCurrency = Axis::config('locale/main/currency');

        // add currency symbols to totals
        foreach ($data as &$_o) {
            $_o['order_total_base'] = $_o['order_total'] . ' ' . $mainStoreCurrency;

            // find the precision of user selected currency
            $precision = $modelCurrency->getData($_o['currency'], 'currency_precision');
            if (empty($precision)) {
                $precision = 2;
            }
            $_o['order_total_customer'] =
                round($_o['order_total_customer'], $precision) . ' ' . $_o['currency'];
        }

        return $this->_helper->json
            ->setData($data)
            ->setCount($select->count())
            ->sendSuccess()
        ;
    }

    public function loadAction()
    {
        $orderId = $this->_getParam('orderId');
        $this->view->order = $order = Axis::single('sales/order')
            ->find($orderId)
            ->current();

        $products = $order->getProducts();
        foreach($products as &$product) {
//            $product['price'] =
//                $product['price'] * $order->currency_rate;
//            $product['final_price'] =
//                $product['final_price'] * $order->currency_rate;

            $product['tax_rate'] = 0;
            if ($product['final_price'] > 0) {
                $product['tax_rate'] = $product['tax'] * 100 / $product['final_price'];
            }
        }
        $data['products'] = array_values($products);

        $totals = $order->getTotals();
//        foreach ($totals as &$total) {
//            $total['value'] = $total['value'] * $order->currency_rate;
//        }
        $this->view->totals = $data['totals'] = $totals;


        $data['history'] = $order->getStatusHistory();
        $customer = Axis::single('account/customer')
            ->find($order->customer_id)
            ->current();
        if ($customer instanceof Axis_Db_Table_Row) {
            $data['customer'] = $customer->toArray();
            unset($data['customer']['password']);//paranoid
            $data['customer']['group'] = Axis::single('account/customer_group')
                ->getName($customer->group_id);
        } else {
            $data['customer'] = array(
                'firstname' => '-//-',
                'lastname'  => '-//-',
                'group'     => 'Guest',
                'group_id'  => Axis_Account_Model_Customer_Group::GROUP_GUEST_ID,
                'email'     => $order->customer_email
            );
        }

        $delivery = $order->getDelivery();
        $data['address']['delivery'] = $delivery->toFlatArray();
        $billing = $order->getBilling();
        $data['address']['billing'] = $billing->toFlatArray();

        $this->view->cc = Axis::single('sales/order_creditcard')
            ->find($order->id)
            ->current();
        $form = $this->view->paymentForm($order->payment_method_code, 'view');
        $data['payment'] = array(
            'name' => $order->payment_method,
            'code' => $order->payment_method_code,
            'form' => $form
        );
        $data['shipping'] = array(
            'name' => $order->shipping_method,
            'code' => $order->shipping_method_code
//            'form' => $this->view->shippingForm($order->shipping_method_code, 'view')
        );

        $order = $order->toArray();
        $orderStatusText = Axis::model('sales/option_order_status_text');
        $order['status_name'] = $orderStatusText[$order['order_status_id']];

        $sites = Axis::model('core/option_site');
        $order['site_name'] = $sites[$order['site_id']];

        // convert price with rates that was available
        // during order was created (not current rates)
//        $order['order_total'] = $order['order_total'] * $order['currency_rate'];
        $data['order'] = $order;

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $params = $this->_getAllParams();

        $params['products'] = Zend_Json::decode($params['products']);
        ////////////////////////////////////////////////////////////////////////
        //add new customer
        $isNewBillingAddress = $params['order']['billing_address_type'] == 0;
        $isNewDeliveryAddress = $params['order']['delivery_address_type'] == 0;
        $event = false;
        if (-2 == $params['order']['customer_id']) {
            $customerRawData = array_merge($params['customer'], array(
                'email'     => $params['order']['customer_email'],
                'is_active' => true,
                'site_id'   => $params['order']['site_id']
            ));

            list($customer, $password) = Axis::single('account/customer')
                ->create($customerRawData);
            $event = true;
            $customer->setDetails($customerRawData);

            $params['order']['customer_id'] = $customer->id;
            $isNewBillingAddress = $isNewDeliveryAddress = true;
        }
        if ($params['order']['customer_id'] < 0) {
            $params['order']['customer_id']  = 0;
        }
        ////////////////////////////////////////////////////////////////////////
        // save new customer addresses
        $customerRow = null;
        if ($params['order']['customer_id']
            && ($isNewBillingAddress || $isNewDeliveryAddress)) {

            $customerRow = Axis::single('account/customer')
                ->find($params['order']['customer_id'])
                ->current();
        }
        if ($customerRow instanceof Axis_Db_Table_Row) {
            $o = $params['order'];
            if ($isNewBillingAddress) {
                $address = array(
                    'firstname'         => $o['billing_firstname'],
                    'lastname'          => $o['billing_lastname'],
                    'phone'             => $o['billing_phone'],
                    'fax'               => $o['billing_fax'],
                    'company'           => $o['billing_company'],
                    'street_address'    => $o['billing_street_address'],
                    'suburb'            => $o['billing_suburb'],
                    'city'              => $o['billing_city'],
                    'postcode'          => $o['billing_postcode'],
                    'country_id'        => $o['billing_country']
                );
                if (!is_numeric($o['billing_state'])){
                    $address['state'] = $o['billing_state'];
                } else {
                    $address['zone_id'] = $o['billing_state'];
                }
                $customerRow->setAddress($address);
            }
            if ($isNewDeliveryAddress) {
                $address = array(
                    'firstname'         => $o['delivery_firstname'],
                    'lastname'          => $o['delivery_lastname'],
                    'phone'             => $o['delivery_phone'],
                    'fax'               => $o['delivery_fax'],
                    'company'           => $o['delivery_company'],
                    'street_address'    => $o['delivery_street_address'],
                    'suburb'            => $o['delivery_suburb'],
                    'city'              => $o['delivery_city'],
                    'postcode'          => $o['delivery_postcode'],
                    'country_id'        => $o['delivery_country']
                );
                if (!is_numeric($o['delivery_state'])){
                    $address['state'] = $o['delivery_state'];
                } else {
                    $address['zone_id'] = $o['delivery_state'];
                }
                $customerRow->setAddress($address);
            }
        }
        if ($event) {
            Axis::dispatch('account_customer_register_success', array(
                'customer' => $customer,
                'password' => $password
            ));
        }

        ////////////////////////////////////////////////////////////////////////
        //prepare order data
        $params['order']['currency_rate'] = Axis::single('locale/currency')
            ->getRateByCode($params['order']['currency']);

        $params['order']['billing_country'] = Axis::single('location/country')
            ->getName($params['order']['billing_country']);

        if (is_numeric($params['order']['billing_state'])
            && ($name = Axis::single('location/zone')->getName($params['order']['billing_state']))) {

            $params['order']['billing_state'] = $name;
        }

        $params['order']['delivery_country'] = Axis::single('location/country')
            ->getName($params['order']['delivery_country']);

        if (is_numeric($params['order']['delivery_state'])
            && ($name = Axis::single('location/zone')->getName($params['order']['delivery_state']))) {

            $params['order']['delivery_state'] = $name;
        }

        if (empty($params['order']['order_status_id'])) {
            $params['order']['order_status_id'] = 0;
        }

        if (empty($params['order']['ip_address'])) {
            $params['order']['ip_address'] = '127.0.0.1';
        }

        $orderRow = Axis::single('sales/order')
            ->find($params['order']['id'])
            ->current();
        if (!$orderRow instanceof Axis_Sales_Model_Order_Row) {
            unset($params['order']['id']);
            $params['order']['locale'] = Axis::config('locale/main/locale');
            $orderRow = Axis::single('sales/order')
                ->createRow($params['order']);
        } else {
            // Unset updated currency rate.
            // It cannot be changed in already placed order.
            if ($params['order']['currency'] === $orderRow->currency) {
                unset($params['order']['currency_rate']);
            }
            $orderRow->setFromArray($params['order']);
        }
        $orderRow->save();

        ////////////////////////////////////////
        // update products
        $modelOrderProduct = Axis::single('sales/order_product');
        $modelOrderProduct->delete(
            Axis::db()->quoteInto(
                'order_id = ?', $orderRow->id
        ));
        foreach ($params['products'] as $product) {
            $modelOrderProduct->add($product, $orderRow->id);
        }
        ////////////////////////////////////////
        //update totals
        $modelOrderTotal = Axis::single('sales/order_total');
        $modelTotal = Axis::single('checkout/total');
        $modelOrderTotal->delete(
            Axis::db()->quoteInto(
                'order_id = ?', $orderRow->id
        ));

        foreach ($params['totals'] as $totalCode => $total) {
            $method = $modelTotal->getMethod($totalCode);
            $modelOrderTotal->insert(
                array(
                'order_id' => $orderRow->id,
                'code'     => $totalCode, // $method->getCode(),
                'title'    => $method->getTitle(),
                'value'    => $total
            ));
        }
        ////////////////////////////////////////
        // STATUS UPDATE
        if(!empty($params['history']['order_status_id'])) {
            $orderRow->setStatus(
                $params['history']['order_status_id'],
                $params['history']['comments'],
                isset($params['history']['notified'])
            );
        } elseif(!empty($params['history']['comments'])) {
            $orderRow->addComment(
                $params['history']['comments'],
                isset($params['history']['notified'])
            );
        }

        return $this->_helper->json
            ->setData(array('order_id' => $orderRow->id))
            ->sendSuccess()
        ;
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        Axis::single('sales/order')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        Axis::message()->addSuccess(
            Axis::translate('sales')->__(
                'Order was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function printAction()
    {
        $this->_helper->layout->disableLayout();
        $orderIds = Zend_Json::decode($this->_getParam('data'));
        if (!sizeof($orderIds)) {
            Axis::message()->addError(
                Axis::translate('sales')->__(
                    'No data to print'
                )
            );
            return $this->_redirect('sales/order');
        }
        asort($orderIds);

        $where = Axis::db()->quoteInto("id IN(?)", $orderIds);
        $rowset = array();
        foreach (Axis::single('sales/order')->fetchAll($where) as $row) {
            $rowset[$row->id] = $row;
        }

        $orders = array();
        $firstOrderId = current($orderIds);

        foreach ($orderIds as $orderId) {
            $orders[$orderId]                = $rowset[$orderId]->toArray();
            $orders[$orderId]['products']    = $rowset[$orderId]->getProducts();
            $orders[$orderId]['totals']      = $rowset[$orderId]->getTotals();
            $orders[$orderId]['billing']     = $rowset[$orderId]->getBilling();
            $orders[$orderId]['delivery']    = $rowset[$orderId]->getDelivery();
            $orders[$orderId]['shipping']    = $rowset[$orderId]->getShipping();
            $orders[$orderId]['company']     = Axis::single('core/site')
                ->getCompanyInfo($rowset[$orderId]->site_id);
        }

        $this->view->label = $this->_getParam('label', 'false');
        $pdfFileName =  'order-';
        if ($this->view->label != 'true') {
            $this->view->invoice = $this->_getParam('invoice', 'true');
            $this->view->packingslip = $this->_getParam('packingslip', 'false');
            $pdfFileName .= ($this->view->invoice === 'true' ? 'invoice-' : '')
                         . ($this->view->packingslip === 'true' ? 'packingslip-' : '');
        } else {
            $this->view->addressType = $this->_getParam('addressType', 'billing');
            $pdfFileName .= $this->view->addressType . '-';
        }

        $this->view->orders = $orders;
        $script = $this->getViewScript('print', false);
        $content = $this->view->render($script);

        if ('pdf' === $this->_getParam('output', 'html')) {
            $pdf = new Axis_Pdf();
            $pdf->setContent($content);
            $pdf->getPdf($pdfFileName
                . ($firstOrderId != end($orderIds) ?
                    $firstOrderId . '-' . end($orderIds) : $firstOrderId)
                . '.pdf'
            );
        } else {
            echo $content;
        }
    }

    public function addProductAction()
    {
        $params = Zend_Json::decode($this->_getParam('data'));

        $data = array();
        foreach ($params as $param) {
            $productId = $param['id'];
            $product = Axis::single('catalog/product')
                ->find($productId)
                ->current();
            if (!$product instanceof Axis_Catalog_Model_Product_Row) {
                Axis::message()->addError(
                    Axis::translate('catalog')->__(
                        'Product not found'
                ));
                return $this->_helper->json->sendFailure();
            }

            $quantity    = $param['quantity'];
            $variationId = $param['variationId'];
            $orderId     = $param['orderId'];
            if (!$product->getStockRow()->canAddToCart($quantity, $variationId)) {
                return $this->_helper->json->sendFailure();
            }

            $stock = Axis::single('catalog/product_stock')
                ->find($productId)
                ->current();

            $modifierAttributes = Axis::single('catalog/product_attribute')
                ->getAttributesByModifiers($product->id, $param['modifiers']);

            $variationAttributes = array_fill_keys(
                Axis::single('catalog/product_attribute')
                    ->select('id')
                    ->where('modifier <> 1')
                    ->where('product_id = ?', $product->id)
                    ->where('variation_id = ?', $variationId)
                    ->fetchCol(),
                null
            );

            $attributes = $modifierAttributes + $variationAttributes;

            $attributesOptions = array();
            if (!empty ($attributes)) {
                $attributesOptions = Axis::single('catalog/product_attribute')
                    ->select('id')
                    ->join(
                        'catalog_product_option_text',
                        'cpa.option_id = cpot.option_id AND ' .
                        Axis::db()->quoteInto(
                            'cpot.language_id = ?', Axis_Locale::getLanguageId()),
                        array('product_option' => 'cpot.name')
                    )
                    ->join(
                        'catalog_product_option_value_text',
                        'cpa.option_value_id = cpovt.option_value_id AND ' .
                        Axis::db()->quoteInto(
                            'cpovt.language_id = ?', Axis_Locale::getLanguageId()),
                        array('product_option_value' => 'cpovt.name')
                    )
                    ->where('id IN(?)', array_keys($attributes))
                    ->fetchAll();
            }


            $finalPrice  = $product->getPrice(array_keys($attributes));
            $finalWeight = $product->getWeight(array_keys($attributes));

            $countryId   = $param['countryId'];
            $zoneId      = empty($param['zoneId']) ? 0 : $param['zoneId'];
            $geozoneIds  = Axis::single('location/geozone')
                ->getIds($countryId, $zoneId);

            $customerGroupId = $param['customerGroupId'];

            $productTax  = Axis::single('tax/rate')->calculateByPrice(
                $finalPrice, $product->tax_class_id, $geozoneIds, $customerGroupId
            );

            $descriptionRow = $product->getDescription();
            $taxRate = 0;
            if ($finalPrice > 0) {
                $taxRate = $productTax * 100 / $finalPrice;
            }
            $data[] = array(
                'attributes'    => $attributesOptions,
                'backorder'     => $stock->backorder,
                'final_price'   => $finalPrice,
                'final_weight'  => $finalWeight,
                'order_id'      => $orderId,
                'product_id'    => $product->id,
                'name'          => $descriptionRow['name'],
                'price'         => $product->price,
                'quantity'      => $quantity,
                'sku'           => $product->sku,
                'tax_rate'      => $taxRate,
                'variation_id'  => $variationId
            );
        }
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
}