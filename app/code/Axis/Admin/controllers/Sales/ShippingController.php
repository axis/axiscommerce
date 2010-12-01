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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Sales_ShippingController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        function _calculateShippingTax(
            $price, Axis_Method_Shipping_Model_Abstract $shipping, array $params)
        {
            $customerGroupId = $params['customer_group_id'];
            if (empty($customerGroupId)) {
                return 0;
            }

            if (!$taxClassId = $shipping->config()->taxClass) {
                if (!$taxClassId = Axis::config()->tax->shipping->taxClass) {
                    return 0;
                }
            }

            if (!$taxBasis = $shipping->config()->taxBasis) {
                if (!$taxBasis = Axis::config()->tax->shipping->taxBasis) {
                    return 0;
                }
            }

            if ('billing' === $taxBasis) {
                $countryId = $params['billing_country_id'];
                $zoneId    = $params['billing_zone_id'];
            } else {
                $countryId = $params['delivery_country_id'];
                $zoneId    = $params['delivery_zone_id'];
            }
            if (empty($zoneId)) {
                $zoneId = null;
            }
            $geozoneIds =
                Axis::single('location/geozone')->getIds($countryId, $zoneId);

            if (empty($geozoneIds)) {
                return 0;
            }

            return Axis::single('tax/rate')->calculateByPrice(
                $price, $taxClassId, $geozoneIds, $customerGroupId
            );
        }

        $this->_helper->layout->disableLayout();
        $params = $this->_getAllParams();

        $countryId = (int) $this->_getParam('delivery_country_id', 0);
        $zoneId    = (int) $this->_getParam('delivery_zone_id', 0);
        $quantity  = (float) $this->_getParam('quantity', 0);
        $weight    = (float) $this->_getParam('weight', 0);
        $subtotal  = (float) $this->_getParam('subtotal', 0);
        $paymentMethodCode = $this->_getParam('payment_method_code', null);
        if (empty($paymentMethodCode)) {
            $paymentMethodCode = null;
        }
        $postcode  = $this->_getParam('postcode', null);

        $request = array(
            'boxes'                => 1,
            'qty'                  => $quantity,
            'weight'               => $weight,
            'price'                => $subtotal,
            'payment_method_code'  => $paymentMethodCode,
            'postcode'             => $postcode
        );

        $request['country'] = Axis::model('location/country')
            ->find($countryId)
            ->current()
            ->toArray();
        $request['zone'] = Axis::model('location/zone')
            ->find($zoneId)
            ->current()
            ->toArray();
        
        $allowedMethods = Axis_Shipping::getAllowedMethods($request);
        $result = array();
        foreach ($allowedMethods['methods'] as $methodCode => $types) {
            $shipping = Axis_Shipping::getMethod($methodCode);
            $title = $shipping->getTitle();
            foreach ($types as $type) {
                $result[] = array(
                    'code'  => $type['id'],
                    'title' => $type['title'],
                    'name'  => $title . ' ' . $type['title'] . ' ' . $type['price'],
                    'price' => $type['price'],
                    'tax'   => _calculateShippingTax(
                        $type['price'], $shipping, $params
                    )
                );
            }
        }
        
        return $this->_helper->json
            ->setData($result)
            ->sendSuccess();
    }

//    public function getForm()
//    {
//
//        $this->_helper->layout->disableLayout();
//        $orderId = $this->_getParam('order[id]', false);
//
//    }
}
