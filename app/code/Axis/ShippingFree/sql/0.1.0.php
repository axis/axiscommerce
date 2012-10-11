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
 * @package     Axis_ShippingFree
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingFree_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $this->getConfigBuilder()
            ->section('shipping', 'Shipping Methods')
                ->setTranslation('Axis_Admin')
                ->section('Free_Standard', 'Free Shipping Standard')
                    ->setTranslation('Axis_ShippingFree')
                    ->option('enabled', 'Enabled')
                        ->setType('radio')
                        ->setModel('core/option_boolean', false)
                        ->setTranslation('Axis_Core')
                    ->option('geozone', 'Allowed Shipping Zone', 1)
                        ->setType('select')
                        ->setDescription('Shipping method will be available only for selected zone')
                        ->setModel('location/option_geozone')
                        ->setTranslation('Axis_Admin')
                    ->option('taxBasis', 'Tax Basis')
                        ->setValue(Axis_Tax_Model_Option_Basis::SHIPPING)
                        ->setType('select')
                        ->setDescription('Address that will be used for tax calculation')
                        ->setModel('tax/option_basis')
                        ->setTranslation('Axis_Tax')
                    ->option('taxClass', 'Tax Class')
                        ->setType('select')
                        ->setDescription('Tax class that will be used for tax calculation')
                        ->setModel('tax/option_class')
                        ->setTranslation('Axis_Tax')
                    ->option('sortOrder', 'Sort Order')
                        ->setTranslation('Axis_Core')
                    ->option('handling', 'Handling Fee')
                    ->option('cost', 'Free Shipping Cost')
                    ->option('payments', 'Disallowed Payments')
                        ->setType('multiple')
                        ->setDescription('Selected payment methods will be not available with this shipping method')
                        ->setModel('checkout/option_payment')
                        ->setTranslation('Axis_Admin')

            ->section('/');
    }

    public function down()
    {
        $this->getConfigBuilder()
            ->remove('shipping/Free_Standard');
    }
}