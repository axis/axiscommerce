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
 * @package     Axis_ShippingFedex
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingFedex_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        Axis::single('core/config_field')
            ->add('shipping', 'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/Fedex_Standard', 'Shipping Methods/Fedex', null, null, array('translation_module' => 'Axis_ShippingFedex'))
            ->add('shipping/Fedex_Standard/enabled', 'Shipping Methods/Fedex/Enabled', '0', 'bool', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Fedex_Standard/geozone', 'Allowed Shipping Zone', '1', 'select', 'Shipping method will be available only for selected zone', array('model' => 'Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Fedex_Standard/taxBasis', 'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'TaxBasis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Fedex_Standard/taxClass', 'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'TaxClass', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Fedex_Standard/sortOrder', 'Sort Order', '0', 'string', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Fedex_Standard/payments', 'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'Payment', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Fedex_Standard/title', 'Title', 'Fedex Express')
            ->add('shipping/Fedex_Standard/account', 'Account Id', '', 'handler', '', array('model' => 'Crypt'))
            ->add('shipping/Fedex_Standard/package', 'Package', 'YOURPACKAGING', 'select',
                'FEDEXENVELOPE - FedEx Envelope, FEDEXPAK  - FedEx Pak, FEDEXBOX - FedEx Box, FEDEXTUBE - FedEx Tube, FEDEX10KGBOX - FedEx 10kg Box, FEDEX25KGBOX - FedEx 25kg Box, YOURPACKAGING - Your Packaging',
                array('config_options' => 'FEDEXENVELOPE,FEDEXPAK,FEDEXBOX,FEDEXTUBE,FEDEX10KGBOX,FEDEX25KGBOX,YOURPACKAGING')
            )
            ->add('shipping/Fedex_Standard/dropoff', 'Dropoff', 'REGULARPICKUP', 'select',
                'REGULARPICKUP - Regular Pickup, REQUESTCOURIER - Request Courier, DROPBOX - Drop Box, BUSINESSSERVICECENTER - Business Service Center, STATION - Station',
                array('config_options' => 'REGULARPICKUP,REQUESTCOURIER,DROPBOX,BUSINESSSERVICECENTER,STATION')
            )
            ->add('shipping/Fedex_Standard/allowedTypes', 'Allowed methods', 'REGULARPICKUP', 'multiple',
                'PRIORITYOVERNIGHT - Priority Overnight, STANDARDOVERNIGHT - Standard Overnight, FIRSTOVERNIGHT - First Overnight, FEDEX2DAY - 2Day,FEDEXEXPRESSSAVER - Express Saver,INTERNATIONALPRIORITY - International Priority,INTERNATIONALECONOMY - International Economy,INTERNATIONALFIRST - International First,FEDEX1DAYFREIGHT - 1 Day Freight, FEDEX2DAYFREIGHT - 2 Day Freight, FEDEX3DAYFREIGHT - 3 Day Freight, FEDEXGROUND - Ground, GROUNDHOMEDELIVERY - Home Delivery, INTERNATIONALPRIORITY FREIGHT - Intl Priority Freight, INTERNATIONALECONOMY FREIGHT - Intl Economy Freight, EUROPEFIRSTINTERNATIONALPRIORITY - Europe First Priority',
                array('config_options' => 'PRIORITYOVERNIGHT,STANDARDOVERNIGHT,FIRSTOVERNIGHT,FEDEX2DAY,FEDEXEXPRESSSAVER,INTERNATIONALPRIORITY,INTERNATIONALECONOMY,INTERNATIONALFIRST,FEDEX1DAYFREIGHT, FEDEX2DAYFREIGHT,FEDEX3DAYFREIGHT,FEDEXGROUND,GROUNDHOMEDELIVERY,INTERNATIONALPRIORITY FREIGHT, INTERNATIONALECONOMY FREIGHT,EUROPEFIRSTINTERNATIONALPRIORITY')
            )
            ->add('shipping/Fedex_Standard/measure', 'UPS Weight Unit', 'LBS', 'select', 'LBS or KGS', array('config_options' => 'LBS,KGS'))
            ->add('shipping/Fedex_Standard/residenceDelivery', 'Residential Delivery', '0', 'bool')
            ->add('shipping/Fedex_Standard/gateway', 'Fedex Gateway Url', 'https://gateway.fedex.com/GatewayDC')
            ->add('shipping/Fedex_Standard/handling', 'Handling Fee', '0', 'string', '')
        ;
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        //Axis::single('core/config_value')->remove('shipping/Fedex_Express');
        //Axis::single('core/config_field')->remove('shipping/Fedex_Express');
        //Axis::single('core/config_value')->remove('shipping/Fedex_Ground');
        //Axis::single('core/config_field')->remove('shipping/Fedex_Ground');

        Axis::single('core/config_value')->remove('shipping/Fedex_Standard');
        Axis::single('core/config_field')->remove('shipping/Fedex_Standard');
    }
}