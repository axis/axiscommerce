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
class Axis_Admin_Sales_PaymentController extends Axis_Admin_Controller_Back
{
   
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $countryId = (int) $this->_getParam('country_id', 0);
        $zoneId    = (int) $this->_getParam('zone_id', 0);
        $quantity  = (float) $this->_getParam('quantity', 0);
        $weight    = (float) $this->_getParam('weight', 0);
        $subtotal  = (float) $this->_getParam('subtotal', 0);
        $shippingMethodCode = $this->_getParam('shipping_method_code', null);
        if (empty($shippingMethodCode)) {
            $shippingMethodCode = null;
        }
        $request = array(
            'boxes'                => 1,
            'qty'                  => $quantity,
            'weight'               => $weight,
            'price'                => $subtotal,
            'shipping_method_code' => $shippingMethodCode
        );
        $request['country'] = Axis::model('location/country')
            ->find($countryId)
            ->current()
            ->toArray();
        $request['zone'] = Axis::model('location/zone')
            ->find($zoneId)
            ->current()
            ->toArray();

        $allowedMethods = Axis_Payment::getAllowedMethods($request);
        
        $data = array();
        foreach ($allowedMethods['methods'] as $method) {
            $data[] = array(
                'code' => $method['code'],
                'name' => $method['title']
            );
        }
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
}
