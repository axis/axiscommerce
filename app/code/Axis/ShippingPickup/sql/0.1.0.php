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
 * @package     Axis_ShippingPickup
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingPickup_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('shipping', 'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/Pickup_Standard', 'Shipping Methods/Pickup Standard', null, null, array('translation_module' => 'Axis_ShippingPickup'))
            ->add('shipping/Pickup_Standard/enabled', 'Shipping Methods/Pickup Standard/Enabled', '0', 'radio', '', array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('shipping/Pickup_Standard/geozone', 'Allowed Shipping Zone', '1', 'select', 'Shipping method will be available only for selected zone', array('model' => 'Axis_Location_Model_Option_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Pickup_Standard/taxBasis', 'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Option_Basis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Pickup_Standard/taxClass', 'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Option_Class', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Pickup_Standard/sortOrder', 'Sort Order', '0', 'text', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Pickup_Standard/handling', 'Handling Fee', '0')
            ->add('shipping/Pickup_Standard/price', 'Shipping Cost', '0', 'text', 'The shipping cost for all orders using this shipping method.')
            ->add('shipping/Pickup_Standard/payments', 'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'Axis_Checkout_Model_Option_Payment', 'translation_module' => 'Axis_Admin'));
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('shipping/Pickup_Standard');
        Axis::single('core/config_field')->remove('shipping/Pickup_Standard');
    }
}