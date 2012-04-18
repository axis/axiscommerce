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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingUsps_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_builder')
            ->section('shipping', 'Shipping Methods')
                ->setTranslation('Axis_Admin')
                ->section('Usps_Standard', 'Usps Standard')
                    ->setTranslation('Axis_ShippingUsps')
                    ->option('enabled', 'Enabled')
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Core')
                    ->option('taxClass', 'Tax Class')
                        ->setType('select')
                        ->setDescription('Tax class that will be used for tax calculation')
                        ->setModel('tax/option_class')
                        ->setTranslation('Axis_Tax')
                    ->option('taxBasis', 'Tax Basis')
                        ->setType('select')
                        ->setDescription('Address that will be used for tax calculation')
                        ->setModel('tax/option_basis')
                        ->setTranslation('Axis_Tax')
                    ->option('geozone', 'Allowed Shipping Zone', '1')
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
                    ->option('service', 'Allowed Service', 'ALL')
                        ->setType('multiple')
                        ->setModel('shippingUsps/option_standard_service')
                    ->option('userId', 'User ID')
                        ->setModel('core/option_crypt')
                    ->option('container', 'Container', 'VARIABLE')
                        ->setType('select')
                        ->setModel('shippingUsps/option_standard_package')
                    ->option('size', 'Size', 'REGULAR')
                        ->setType('select')
                        ->setModel('shippingUsps/option_standard_size')
                    ->option('machinable', 'Machinable', '1')
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                    ->option('allowedMethods', 'Allowed Shipping Methods', 'First-Class,First-Class Mail International Large Envelope,First-Class Mail International Letter,First-Class Mail International Package,First-Class Mail,First-Class Mail Flat,First-Class Mail Large Envelope,First-Class Mail International,First-Class Mail Letter,First-Class Mail Parcel,First-Class Mail Package,Parcel Post,Bound Printed Matter,Media Mail,Library Mail,Express Mail,Express Mail PO to PO,Express Mail Flat Rate Envelope,Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee,Express Mail Sunday/Holiday Guarantee,Express Mail Flat Rate Envelope Hold For Pickup,Express Mail Hold For Pickup,Global Express Guaranteed (GXG),Global Express Guaranteed Non-Document Rectangular,Global Express Guaranteed Non-Document Non-Rectangular,USPS GXG Envelopes,Express Mail International,Express Mail International Flat Rate Envelope,Priority Mail,Priority Mail Small Flat Rate Box,Priority Mail Medium Flat Rate Box,Priority Mail Large Flat Rate Box,Priority Mail Flat Rate Box,Priority Mail Flat Rate Envelope,Priority Mail International,Priority Mail International Flat Rate Envelope,Priority Mail International Small Flat Rate Box,Priority Mail International Medium Flat Rate Box,Priority Mail International Large Flat Rate Box,Priority Mail International Flat Rate Box')
                        ->setType('multiple')
                        ->setModel('shippingUsps/option_standard_serviceLabel')

            ->section('/');
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('shipping/Usps_Standard');
        Axis::single('core/config_field')->remove('shipping/Usps_Standard');
    }
}