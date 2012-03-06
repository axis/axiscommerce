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
 * @package     Axis_ShippingPerWeightUnit
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingPerWeightUnit_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('shipping', 'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/PerWeightUnit_Standard', 'Shipping Methods/Per Weight Unit', null, null, array('translation_module' => 'Axis_ShippingPerWeightUnit'))
            ->add('shipping/PerWeightUnit_Standard/enabled', 'Shipping Methods/Per Weight Unit/Enabled', 0, 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('shipping/PerWeightUnit_Standard/geozone', 'Allowed Shipping Zone', 1, 'select', 'Shipping method will be available only for selected zone', array('model' => 'Axis_Location_Model_Option_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/PerWeightUnit_Standard/taxBasis', 'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Option_Basis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/PerWeightUnit_Standard/taxClass', 'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Option_Class', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/PerWeightUnit_Standard/sortOrder', 'Sort Order', '0', 'string', array('translation_module' => 'Axis_Core'))
            ->add('shipping/PerWeightUnit_Standard/handling', 'Handling Fee', '0')
            ->add('shipping/PerWeightUnit_Standard/price', 'Shipping Cost per Unit', '1', 'string', 'NOTE: When using this Shipping Module be sure to check the Tare settings in the Shipping/Packaging and set the Largest Weight high enough to handle the price, such as 5000.00 and the adjust the settings on Small and Large packages which will add to the price as well.The shipping cost will be used to determin shipping charges based on: Product Quantity * Units (products_weight) * Cost per Unit - in an order that uses this shipping method.')
            ->add('shipping/PerWeightUnit_Standard/payments', 'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'Axis_Checkout_Model_Option_Payment', 'translation_module' => 'Axis_Admin'));
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('shipping/PerWeightUnit_Standard');
        Axis::single('core/config_field')->remove('shipping/PerWeightUnit_Standard');
    }
}