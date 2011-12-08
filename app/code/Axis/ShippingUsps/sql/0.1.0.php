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
        Axis::single('core/config_field')
            ->add('shipping', 'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/Usps_Standard', 'Shipping Methods/Usps Standard', null, null, array('translation_module' => 'Axis_ShippingUsps'))
            ->add('shipping/Usps_Standard/enabled', 'Shipping Methods/Usps Standard/Enabled', '0', 'bool', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Usps_Standard/taxClass', 'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Class', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Usps_Standard/taxBasis', 'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Basis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Usps_Standard/geozone', 'Allowed Shipping Zone', '1', 'select', 'Shipping method will be available only for selected zone', array('model' => 'Axis_Location_Model_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Usps_Standard/handling', 'Handling price', '5')
            ->add('shipping/Usps_Standard/sortOrder', 'Sort Order', '0', 'string', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Usps_Standard/payments', 'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'Axis_Sales_Model_Payment', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Usps_Standard/title', 'Title', 'United States Postal Service')

            ->add('shipping/Usps_Standard/gateway', 'Gateway Url', 'http://production.shippingapis.com/ShippingAPI.dll')
            ->add('shipping/Usps_Standard/service', 'Allowed Service', 'FIRST CLASS,PRIORITY,EXPRESS,BPM,PARCEL,MEDIA,LIBRARY', 'multiple', array('config_options' => '{"FIRST CLASS":"First-Class","PRIORITY":"Priority Mail","EXPRESS":"Express Mail","BPM":"Bound Printed Matter","PARCEL":"Parcel Post","MEDIA":"Media Mail","LIBRARY":"Library"}'))
            ->add('shipping/Usps_Standard/userId', 'User ID', '', 'handler', '', array('model' => 'Crypt'))
            ->add('shipping/Usps_Standard/container', 'Container', 'VARIABLE', 'select', '', array('config_options' => '{"VARIABLE":"Variable","FLAT RATE BOX":"Flat-Rate Box","FLAT RATE ENVELOPE":"Flat-Rate Envelope" ,"RECTANGULAR":"Rectangular","NONRECTANGULAR":"Non-rectangular"}'))
            ->add('shipping/Usps_Standard/size', 'Size', 'REGULAR', 'select', '', array('config_options' => '{"REGULAR":"Regular","LARGE":"Large","OVERSIZE":"Oversize"}'))
            ->add('shipping/Usps_Standard/machinable', 'Machinable', '1', 'bool')
            ->add('shipping/Usps_Standard/allowedMethods', 'Allowed Shipping Methods', 'Bound Printed Matter,Express Mail,Express Mail Flat-Rate Envelope,Express Mail Flat-Rate Envelope Hold For Pickup,Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee,Express Mail Hold For Pickup,Express Mail International (EMS),Express Mail International (EMS) Flat-Rate Envelope,Express Mail PO to PO,Express Mail Sunday/Holiday Guarantee,Express Mail to PO Addressee,First Class Mail International Large Envelope,First Class Mail International Letters,First Class Mail International Package,First-Class,First-Class Mail,First-Class Mail Flat,First-Class Mail International,First-Class Mail Letter,First-Class Mail Parcel,Global Express Guaranteed,Global Express Guaranteed Non-Document Non-Rectangular,Global Express Guaranteed Non-Document Rectangular,Library Mail,Media Mail,Parcel Post,Priority Mail,Priority Mail Flat-Rate Box,Priority Mail Flat-Rate Envelope,Priority Mail International,Priority Mail International Flat-Rate Box,Priority Mail International Flat-Rate Envelope,Priority Mail International Large Flat-Rate Box,Priority Mail Large Flat-Rate Box,USPS GXG Envelopes', 'multiple', array('config_options' => 'Bound Printed Matter,Express Mail,Express Mail Flat-Rate Envelope,Express Mail Flat-Rate Envelope Hold For Pickup,Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee,Express Mail Hold For Pickup,Express Mail International (EMS),Express Mail International (EMS) Flat-Rate Envelope,Express Mail PO to PO,Express Mail Sunday/Holiday Guarantee,First Class Mail International Large Envelope,First Class Mail International Letters,First Class Mail International Package,First-Class,First-Class Mail,First-Class Mail Flat,First-Class Mail International,First-Class Mail Letter,First-Class Mail Parcel,Global Express Guaranteed,Global Express Guaranteed Non-Document Non-Rectangular,Global Express Guaranteed Non-Document Rectangular,Library Mail,Media Mail,Parcel Post,Priority Mail,Priority Mail Flat-Rate Box,Priority Mail Flat-Rate Envelope,Priority Mail International,Priority Mail International Flat-Rate Box,Priority Mail International Flat-Rate Envelope,Priority Mail International Large Flat-Rate Box,Priority Mail Large Flat-Rate Box,USPS GXG Envelopes'))
            ;
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('shipping/Usps_Standard');
        Axis::single('core/config_field')->remove('shipping/Usps_Standard');
    }
}