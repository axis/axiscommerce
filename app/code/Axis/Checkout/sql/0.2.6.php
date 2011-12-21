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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Checkout_Upgrade_0_2_6 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.6';

    public function up()
    {
        $models = array(
            //@todo move model checkout
//            'Payment'                     => 'Axis_Sales_Model_Payment',
//            'Shipping'                    => 'Axis_Sales_Model_Shipping'
        ); 
        
        $paths = array(
            'checkout/address_form/custom_fields_display_mode' => 'Axis_Checkout_Model_Form_Addess_CustomFieldsDisplayMode',
            'checkout/cart/redirect'                           => 'Axis_Checkout_Model_Cart_Redirect'
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($paths[$row->path])) {
                $row->config_options = null; 
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
    }
}