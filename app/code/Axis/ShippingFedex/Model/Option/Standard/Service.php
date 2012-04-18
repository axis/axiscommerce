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
 * @subpackage  Axis_ShippingFedex_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingFedex
 * @subpackage  Axis_ShippingFedex_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_ShippingFedex_Model_Option_Standard_Service extends Axis_Config_Option_Array_Multi
{
    const EUROPE_FIRST_INTERNATIONAL_PRIORITY = 'EUROPEFIRSTINTERNATIONALPRIORITY';
    const FEDEX_1_DAY_FREIGHT                 = 'FEDEX1DAYFREIGHT';
    const FEDEX_2_DAY_FREIGHT                 = 'FEDEX2DAYFREIGHT';
    const FEDEX_2_DAY                         = 'FEDEX2DAY';
    const FEDEX_3_DAY_FREIGHT                 = 'FEDEX3DAYFREIGHT';
    const FEDEX_EXPRESS_SAVER                 = 'FEDEXEXPRESSSAVER';
    const FEDEX_GROUND                        = 'FEDEXGROUND';
    const FIRST_OVERNIGHT                     = 'FIRSTOVERNIGHT';
    const GROUND_HOME_DELIVERY                = 'GROUNDHOMEDELIVERY';
    const INTERNATIONAL_ECONOMY               = 'INTERNATIONALECONOMY';
    const INTERNATIONAL_ECONOMY_FREIGHT       = 'INTERNATIONALECONOMY FREIGHT';
    const INTERNATIONAL_FIRST                 = 'INTERNATIONALFIRST';
    const INTERNATIONAL_GROUND                = 'INTERNATIONAL_GROUND';
    const INTERNATIONAL_PRIORITY              = 'INTERNATIONALPRIORITY';
    const INTERNATIONAL_PRIORITY_FREIGHT      = 'INTERNATIONALPRIORITY FREIGHT';
    const PRIORITY_OVERNIGHT                  = 'PRIORITYOVERNIGHT';
    const SMART_POST                          = 'SMART_POST';
    const STANDARD_OVERNIGHT                  = 'STANDARDOVERNIGHT';
    const FEDEX_FREIGHT                       = 'FEDEXFREIGHT';
    const FEDEX_NATIONAL_FREIGHT              = 'FEDEX_NATIONAL_FREIGHT';

    /**
     *
     * @return  array
     */
    protected function _loadCollection()
    {
        return array(
            self::EUROPE_FIRST_INTERNATIONAL_PRIORITY => 'Europe First Priority',
            self::FEDEX_1_DAY_FREIGHT                 => '1 Day Freight',
            self::FEDEX_2_DAY_FREIGHT                 => '2 Day Freight',
            self::FEDEX_2_DAY                         => '2 Day',
            self::FEDEX_3_DAY_FREIGHT                 => '3 Day Freight',
            self::FEDEX_EXPRESS_SAVER                 => 'Express Saver',
            self::FEDEX_GROUND                        => 'Ground',
            self::FIRST_OVERNIGHT                     => 'First Overnight',
            self::GROUND_HOME_DELIVERY                => 'Home Delivery',
            self::INTERNATIONAL_ECONOMY               => 'International Economy',
            self::INTERNATIONAL_ECONOMY_FREIGHT       => 'Intl Economy Freight',
            self::INTERNATIONAL_FIRST                 => 'International First',
            self::INTERNATIONAL_GROUND                => 'International Ground',
            self::INTERNATIONAL_PRIORITY              => 'International Priority',
            self::INTERNATIONAL_PRIORITY_FREIGHT      => 'Intl Priority Freight',
            self::PRIORITY_OVERNIGHT                  => 'Priority Overnight',
            self::SMART_POST                          => 'Smart Post',
            self::STANDARD_OVERNIGHT                  => 'Standard Overnight',
            self::FEDEX_FREIGHT                       => 'Freight',
            self::FEDEX_NATIONAL_FREIGHT              => 'National Freight'
        );
    }
}