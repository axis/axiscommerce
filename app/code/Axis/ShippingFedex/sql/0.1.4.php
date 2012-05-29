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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingFedex_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = '';

    public function up()
    {
        $this->getConfigBuilder()
            ->section('shipping')
                ->section('Fedex_Standard')
                    ->setDefaultModel('core/option_crypt')
                    ->option('account', 'Account Number')
                    ->option('meterNumber', 'Meter Number')
                    ->option('key', 'Key')
                    ->option('password', 'Password')
            ->section('/');
        
        //packaging
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'shipping/Fedex_Standard/package')
            ->fetchRowset();
        $replaces = array(
            'FEDEXENVELOPE'     => 'FEDEX_ENVELOPE',
            'FEDEXPAK'          => 'FEDEX_PAK',
            'FEDEXBOX'          => 'FEDEX_BOX',
            'FEDEXTUBE'         => 'FEDEX_TUBE',
            'FEDEX10KGBOX'      => 'FEDEX_10KG_BOX',
            'FEDEX25KGBOX'      => 'FEDEX_25KG_BOX',
            'YOURPACKAGING'     => 'YOUR_PACKAGING'
        );
        foreach ($rowset as $row) {
            $row->value = isset($replaces[$row->value]) ? 
                $replaces[$row->value] : 'YOUR_PACKAGING' ;
            $row->save();
        }
        //dropoff
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'shipping/Fedex_Standard/dropoff')
            ->fetchRowset();
        $replaces = array(
            'REGULARPICKUP'         => 'REGULAR_PICKUP',
            'REQUESTCOURIER'        => 'REQUEST_COURIER',
            'DROPBOX'               => 'DROP_BOX',
            'BUSINESSSERVICECENTER' => 'BUSINESS_SERVICE_CENTER',
            'STATION'               => 'STATION'
        );
        foreach ($rowset as $row) {
            $row->value = isset($replaces[$row->value]) ? 
                $replaces[$row->value] : 'REGULAR_PICKUP' ;
            $row->save();
        }
        //services
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'shipping/Fedex_Standard/allowedTypes')
            ->fetchRowset();
        
        $replaces = array(
            'EUROPEFIRSTINTERNATIONALPRIORITY'  => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY',
            'FEDEX1DAYFREIGHT'                  => 'FEDEX_1_DAY_FREIGHT',
            'FEDEX2DAYFREIGHT'                  => 'FEDEX_2_DAY_FREIGHT',
            'FEDEX2DAY'                         => 'FEDEX_2_DAY',
            'FEDEX3DAYFREIGHT'                  => 'FEDEX_3_DAY_FREIGHT',
            'FEDEXEXPRESSSAVER'                 => 'FEDEX_EXPRESS_SAVER',
            'FEDEXGROUND'                       => 'FEDEX_GROUND',
            'FIRSTOVERNIGHT'                    => 'FIRST_OVERNIGHT',
            'GROUNDHOMEDELIVERY'                => 'GROUND_HOME_DELIVERY',
            'INTERNATIONALECONOMY'              => 'INTERNATIONAL_ECONOMY',
            'INTERNATIONALECONOMY FREIGHT'      => 'INTERNATIONAL_ECONOMY_FREIGHT',
            'INTERNATIONALFIRST'                => 'INTERNATIONAL_FIRST',
            'INTERNATIONALGROUND'               => 'INTERNATIONAL_GROUND',
            'INTERNATIONALPRIORITY'             => 'INTERNATIONAL_PRIORITY',
            'INTERNATIONALPRIORITY FREIGHT'     => 'INTERNATIONAL_PRIORITY_FREIGHT',
            'PRIORITYOVERNIGHT'                 => 'PRIORITY_OVERNIGHT',
            'SMARTPOST'                         => 'SMART_POST',
            'STANDARDOVERNIGHT'                 => 'STANDARD_OVERNIGHT',
            'FEDEXFREIGHT'                      => 'FEDEX_FREIGHT',
            'FEDEXNATIONALFREIGHT'              => 'FEDEX_NATIONAL_FREIGHT'
        );
        foreach ($rowset as $row) {
            $row->value = str_replace(
                array_keys($replaces), array_values($replaces), $row->value
            );
            $row->save();
        }
    }
}
