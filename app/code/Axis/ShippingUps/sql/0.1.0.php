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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingUps_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('shipping',                        'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/Ups_Standard',           'Shipping Methods/Ups Standard', null, null, array('translation_module' => 'Axis_ShippingUps'))
            ->add('shipping/Ups_Standard/enabled',   'Shipping Methods/Ups Standard/Enabled', '0', 'radio', '', array('model'=> 'core/option_boolean', 'translation_module' => 'Axis_Core'))
            ->add('shipping/Ups_Standard/geozone',   'Allowed Shipping Zone', '1', 'select', 'Shipping method will be available only for selected zone', array('model' => 'location/option_geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Ups_Standard/taxBasis',  'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'tax/option_basis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Ups_Standard/taxClass',  'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'tax/option_class', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Ups_Standard/sortOrder', 'Sort Order', '0', 'text', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Ups_Standard/pickup',    'UPS Pickup Method', Axis_ShippingUps_Model_Option_Standard_Pickup::CC, 'select', 'How do you give packages to UPS?', array('model' => 'shippingUps/option_standard_pickup'))
            ->add('shipping/Ups_Standard/package',   'UPS Packaging?',  Axis_ShippingUps_Model_Option_Standard_Package::CP, 'select', 'CP - Your Packaging, ULE - UPS Letter, UT - UPS Tube, UBE - UPS Express Box', array('model' => 'shippingUps/option_standard_package'))
            ->add('shipping/Ups_Standard/res',       'Residential Delivery?', Axis_ShippingUps_Model_Option_Standard_DestinationType::RES, 'select', 'Quote for Residential (RES) or Commercial Delivery (COM)', array('model' => 'shippingUps/option_standard_destinationType'))
            ->add('shipping/Ups_Standard/handling',  'Handling Fee', '0', 'text', '')
            ->add('shipping/Ups_Standard/title',     'Title', 'Ups')
            ->add('shipping/Ups_Standard/types',     'Allowed Shipping Methods', Axis_ShippingUps_Model_Option_Standard_Service::getDeafult(), 'multiple', 'Select the UPS services to be offered. : <br />Nxt AM, Nxt AM Ltr, Nxt, Nxt Ltr, Nxt PR, Nxt Save, Nxt Save Ltr, 2nd AM, 2nd AM Ltr, 2nd, 2nd Ltr, 3 Day Select, Ground, Canada,World Xp, World Xp Ltr, World Xp Plus, World Xp Plus Ltr, World Expedite, WorldWideSaver.',
                array('model' => 'shippingUps/option_standard_service')
            )
            ->add('shipping/Ups_Standard/boxWeightDisplay', 'Shipping/Default/boxWeightDisplay', '1', 'text', 'Variants: 0, 1 or 2 ')
            ->add('shipping/Ups_Standard/type',     'UPS Type', Axis_ShippingUps_Model_Option_Standard_RequestType::CGI, 'select', 'CGI or XML', array('model' => 'shippingUps/option_standard_requestType'))
            ->add('shipping/Ups_Standard/measure',  'UPS Weight Unit', Axis_ShippingUps_Model_Option_Standard_Measure::LBS, 'select', 'LBS or KGS', array('model' => 'shippingUps/option_standard_measure'))
            ->add('shipping/Ups_Standard/payments', 'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'checkout/option_payment', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Ups_Standard/gateway',  'Gateway Url', 'http://www.ups.com/using/services/rave/qcostcgi.cgi')

            ->add('shipping/Ups_Standard/xmlUserId',              'XML Account User Id', '', 'text', '', array('model' => 'core/option_crypt'))
            ->add('shipping/Ups_Standard/xmlPassword',            'XML Account Password', '', 'text', '', array('model' => 'core/option_crypt'))
            ->add('shipping/Ups_Standard/xmlAccessLicenseNumber', 'XML Access License Number', '', 'text', '', array('model' => 'core/option_crypt'))
            ->add('shipping/Ups_Standard/xmlGateway',             'Gateway XML URL', 'https://onlinetools.ups.com/ups.app/xml/Rate')
            ->add('shipping/Ups_Standard/xmlOrigin',              'Origin of the shipment', Axis_ShippingUps_Model_Option_Standard_Origin::getDeafult(), 'select', '', array('model' => 'shippingUps/option_standard_origin'))
            ->add('shipping/Ups_Standard/negotiatedActive',       'Enable Negotiated Rates', '0', 'radio', '', array('model'=> 'core/option_boolean'))
            ->add('shipping/Ups_Standard/shipperNumber',          'Shipper Number', '', 'text', '', array('model' => 'core/option_crypt'))
            ;
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('shipping/Ups_Standard');
        Axis::single('core/config_field')->remove('shipping/Ups_Standard');
    }
}