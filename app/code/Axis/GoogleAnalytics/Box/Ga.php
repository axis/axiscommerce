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
 * @package     Axis_GoogleAnalytics
 * @subpackage  Axis_GoogleAnalytics_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_GoogleAnalytics
 * @subpackage  Axis_GoogleAnalytics_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_GoogleAnalytics_Box_Ga extends Axis_Core_Box_Abstract
{
    protected $_title = '';
    protected $_disableWrapper = true;
    
    public function render() 
    {
        if (!Axis::config('analytics/main/used')) {
            return '';
        }
        $helper = $this->getView()->GoogleAnalytics();
        $helper->_setAccount(Axis::config('analytics/main/uacct'))
            ->_trackPageview();
        /**
         * @var $order Axis_Sales_Model_Order_Row
         */
        $order = $this->getOrder();
        if ($order instanceof Axis_Sales_Model_Order_Row) {
            $total    = number_format($order->order_total, 3, '.', '');
            $tax      = $order->getTax() + $order->getShippingTax();
            $tax      = number_format($tax, 3, '.', '');
            $shipping = number_format($order->getShipping(), 3, '.', '');

            // add transaction
            $helper->_addTrans(
                $order->id,
                null, //affiliation
                $total,
                $tax,
                $shipping,
                $order->billing_city,
                $order->billing_state,
                $order->billing_country
            );
            $modelProduct = Axis::model('catalog/product');
            foreach ($order->getProducts() as $_product) {
                $attrs = '';
                if (isset($_product['attributes'])) {
                    $_attrs = array();
                    foreach ($_product['attributes'] as $_option) {
                        $_attrs[] = $_option['product_option']
                            . ':' . $_option['product_option_value'];
                    }
                    $attrs = "[" . implode(';', $_attrs) . "]";
                }

                $categories = $modelProduct->find($_product['product_id'])
                    ->current()
                    ->getCategories();
                foreach ($categories as &$_category) {
                    $_category = $_category['name'];
                }
                $price = number_format($_product['final_price'], 2, '.', '');
                // add item
                $helper->_addItem(
                    $order->id,
                    $_product['sku'],
                    $_product['name']. ' ' . $attrs ,
                    implode(',', $categories),
                    $price,
                    $_product['quantity']
                );
            }
            $helper->_trackTrans();
        }
        if (!empty($this->customOption)) {
            foreach (array_filter(explode('->', $this->customOption)) as $option) {
                preg_match('/^(_.+)\((.*)\)/', $option, $match);
                $args = explode(
                    ',', str_replace(array(' ', "'", '"'), '', $match[2])
                );
                call_user_func_array(array($helper, $match[1]), $args);
            }
        }
        return $helper->toString();
    }

    public function getConfigurationFields()
    {
        /**
         * @link http://code.google.com/intl/en/apis/analytics/docs/tracking/home.html
         */
        return array(
            'customOption' => array(
                'fieldLabel'   => Axis::translate('GoogleAnalytics')->__(
                    'Custom options'
                ),
                'initialValue' => '' 
                // example ->_setCampNameKey('zzz')->_setCampSourceKey()
            )
        );
    }
}