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
 * @package     Axis_ShippingUps
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingUps_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $this->getConfigBuilder()
            ->section('shipping', 'Shipping Methods')
                ->setTranslation('Axis_Admin')
                ->section('Ups_Standard', 'Ups Standard')
                    ->setTranslation('Axis_ShippingUps')
                    ->option('enabled', 'Enabled', false)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
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
                    ->option('pickup', 'UPS Pickup Method')
                        ->setValue(Axis_ShippingUps_Model_Option_Standard_Pickup::CC)
                        ->setType('select')
                        ->setDescription('How do you give packages to UPS?')
                        ->setModel('shippingUps/option_standard_pickup')
                    ->option('package', 'UPS Packaging?')
                        ->setValue(Axis_ShippingUps_Model_Option_Standard_Package::CP)
                        ->setType('select')
                        ->setDescription('CP - Your Packaging, ULE - UPS Letter, UT - UPS Tube, UBE - UPS Express Box')
                        ->setModel('shippingUps/option_standard_package')
                    ->option('res', 'Residential Delivery?')
                        ->setValue(Axis_ShippingUps_Model_Option_Standard_DestinationType::RES)
                        ->setType('select')
                        ->setDescription('Quote for Residential (RES) or Commercial Delivery (COM)')
                        ->setModel('shippingUps/option_standard_destinationType')
                    ->option('handling', 'Handling Fee')
                    ->option('title', 'Title', 'Ups')
                    ->option('types', 'Allowed Shipping Methods')
                        ->setValue(Axis_ShippingUps_Model_Option_Standard_Service::getDeafult())
                        ->setType('multiple')
                        ->setDescription('Select the UPS services to be offered. : <br />Nxt AM, Nxt AM Ltr, Nxt, Nxt Ltr, Nxt PR, Nxt Save, Nxt Save Ltr, 2nd AM, 2nd AM Ltr, 2nd, 2nd Ltr, 3 Day Select, Ground, Canada,World Xp, World Xp Ltr, World Xp Plus, World Xp Plus Ltr, World Expedite, WorldWideSaver.')
                        ->setModel('shippingUps/option_standard_service')
                    ->option('boxWeightDisplay', 'boxWeightDisplay', 1)
                        ->setDescription('Variants: 0, 1 or 2 ')
                    ->option('type', 'UPS Type')
                        ->setValue(Axis_ShippingUps_Model_Option_Standard_RequestType::CGI)
                        ->setType('select')
                        ->setDescription('CGI or XML')
                        ->setModel('shippingUps/option_standard_requestType')
                    ->option('measure', 'UPS Weight Unit')
                        ->setValue(Axis_ShippingUps_Model_Option_Standard_Measure::LBS)
                        ->setType('select')
                        ->setDescription('LBS or KGS')
                        ->setModel('shippingUps/option_standard_measure')
                    ->option('payments', 'Disallowed Payments')
                        ->setType('multiple')
                        ->setDescription('Selected payment methods will be not available with this shipping method')
                        ->setModel('checkout/option_payment')
                        ->setTranslation('Axis_Admin')
                    ->option('gateway', 'Gateway Url', 'http://www.ups.com/using/services/rave/qcostcgi.cgi')
                    ->option('xmlUserId', 'XML Account User Id')
                        ->setModel('core/option_crypt')
                    ->option('xmlPassword', 'XML Account Password')
                        ->setModel('core/option_crypt')
                    ->option('xmlAccessLicenseNumber', 'XML Access License Number')
                        ->setModel('core/option_crypt')
                    ->option('xmlGateway', 'Gateway XML URL', 'https://onlinetools.ups.com/ups.app/xml/Rate')
                    ->option('xmlOrigin', 'Origin of the shipment')
                        ->setValue(Axis_ShippingUps_Model_Option_Standard_Origin::getDeafult())
                        ->setType('select')
                        ->setModel('shippingUps/option_standard_origin')
                    ->option('negotiatedActive', 'Enable Negotiated Rates', false)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                    ->option('shipperNumber', 'Shipper Number')
                        ->setModel('core/option_crypt')

            ->section('/');
    }

    public function down()
    {
        $this->getConfigBuilder()
            ->remove('shipping/Ups_Standard');
    }
}