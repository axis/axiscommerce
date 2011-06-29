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
 * @package     Axis_ShippingFlat
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingFlat_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        Axis::single('core/config_field')
            ->add('shipping', 'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/Flat_Standard', 'Shipping Methods/Flat Rate Standard', null, null, array('translation_module' => 'Axis_ShippingFlat'))
            ->add('shipping/Flat_Standard/enabled', 'Shipping Methods/Flat Rate Standard/Enabled', 1, 'bool', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Flat_Standard/geozone', 'Allowed Shipping Zone', '1', 'select', 'Shipping method will be available only for selected zone', array('model' => 'Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Flat_Standard/taxBasis', 'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'TaxBasis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Flat_Standard/taxClass', 'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'TaxClass', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Flat_Standard/sortOrder', 'Sort Order', '0', 'string', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Flat_Standard/multiPrice', 'Multi Price', '25', 'handler', '', array('model' => 'ShippingFlatRateMultiPrice'))
            ->add('shipping/Flat_Standard/type', 'Type', 'Per Order', 'select', 'The shipping cost is based on:', array('config_options' => 'Per Order,Per Item'))
            ->add('shipping/Flat_Standard/formDesc', 'Checkout Description', 'Flat Rate')
            ->add('shipping/Flat_Standard/payments', 'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'Payment', 'translation_module' => 'Axis_Admin'));
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        Axis::single('core/config_value')->remove('shipping/Flat_Standard');
        Axis::single('core/config_field')->remove('shipping/Flat_Standard');
    }
}