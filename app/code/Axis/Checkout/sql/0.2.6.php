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
 * @package     Axis_Checkout
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Checkout_Upgrade_0_2_6 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.6';

    public function up()
    {
        $models = array(
            'Payment'  => 'checkout/option_payment',
            'Shipping' => 'checkout/option_shipping'
        ); 
        
        $paths = array(
            'checkout/address_form/custom_fields_display_mode' => 'checkout/option_form_address_customFieldsDisplayMode',
            'checkout/cart/redirect'                           => 'checkout/option_cart_redirect'
        );
        
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            
            if (isset($models[$row->model])) {
                $row->model = $models[$row->model];
                $row->save();
            }
            
            if (isset($paths[$row->path])) {
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
    }
}