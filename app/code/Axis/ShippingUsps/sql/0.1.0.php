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
 * @package     Axis_ShippingUsps
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingUsps_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $this->getConfigBuilder()
            ->section('shipping', 'Shipping Methods')
                ->setTranslation('Axis_Admin')
                ->section('Usps_Standard', 'Usps Standard')
                    ->setTranslation('Axis_ShippingUsps')
                    ->option('enabled', 'Enabled', false)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Core')
                    ->option('taxClass', 'Tax Class')
                        ->setType('select')
                        ->setDescription('Tax class that will be used for tax calculation')
                        ->setModel('tax/option_class')
                        ->setTranslation('Axis_Tax')
                    ->option('taxBasis', 'Tax Basis')
                        ->setValue(Axis_Tax_Model_Option_Basis::SHIPPING)
                        ->setType('select')
                        ->setDescription('Address that will be used for tax calculation')
                        ->setModel('tax/option_basis')
                        ->setTranslation('Axis_Tax')
                    ->option('geozone', 'Allowed Shipping Zone', 1)
                        ->setType('select')
                        ->setDescription('Shipping method will be available only for selected zone')
                        ->setModel('location/option_geozone')
                        ->setTranslation('Axis_Admin')
                    ->option('handling', 'Handling price', '5')
                    ->option('sortOrder', 'Sort Order')
                        ->setTranslation('Axis_Core')
                    ->option('payments', 'Disallowed Payments')
                        ->setType('multiple')
                        ->setDescription('Selected payment methods will be not available with this shipping method')
                        ->setModel('checkout/option_payment')
                        ->setTranslation('Axis_Admin')
                    ->option('title', 'Title', 'United States Postal Service')
                    ->option('gateway', 'Gateway Url', 'http://production.shippingapis.com/ShippingAPI.dll')
                    ->option('service', 'Allowed Service')
                        ->setValue(Axis_ShippingUsps_Model_Option_Standard_Service::getDeafult())
                        ->setType('multiple')
                        ->setModel('shippingUsps/option_standard_service')
                    ->option('userId', 'User ID')
                        ->setModel('core/option_crypt')
                    ->option('container', 'Container')
                        ->setValue(Axis_ShippingUsps_Model_Option_Standard_Package::VARIABLE)
                        ->setType('select')
                        ->setModel('shippingUsps/option_standard_package')
                    ->option('size', 'Size')
                        ->setValue(Axis_ShippingUsps_Model_Option_Standard_Size::REGULAR)
                        ->setType('select')
                        ->setModel('shippingUsps/option_standard_size')
                    ->option('machinable', 'Machinable', true)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                    ->option('allowedMethods', 'Allowed Shipping Methods')
                        ->setValue(Axis_ShippingUsps_Model_Option_Standard_ServiceLabel::getDeafult())
                        ->setType('multiple')
                        ->setModel('shippingUsps/option_standard_serviceLabel')

            ->section('/');
    }

    public function down()
    {
        $this->getConfigBuilder()
            ->remove('shipping/Usps_Standard');
    }
}