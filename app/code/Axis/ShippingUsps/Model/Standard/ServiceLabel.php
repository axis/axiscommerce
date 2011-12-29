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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.If not, see <http://www.gnu.org/licenses/>.
 *
 * @categoryAxis
 * @package Axis_ShippingUsps
 * @subpackageAxis_ShippingUsps_Model
 * @copyright Copyright 2008-2011 Axis
 * @license GNU Public License V3.0
 */

/**
 *
 * @categoryAxis
 * @package Axis_ShippingUsps
 * @subpackageAxis_ShippingUsps_Model
 * @authorAxis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_ShippingUsps_Model_Standard_ServiceLabel extends Axis_ShippingUsps_Model_Standard_Service
{
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        $_labels = array(
            'First-Class',
            'First-Class Mail International Large Envelope',
            'First-Class Mail International Letter',
            'First-Class Mail International Package' ,
            'First-Class Mail',
            'First-Class Mail Flat' ,
            'First-Class Mail Large Envelope' ,
            'First-Class Mail International',
            'First-Class Mail Letter' ,
            'First-Class Mail Parcel' ,
            'First-Class Mail Package',
            'Parcel Post',
            'Bound Printed Matter' ,
            'Media Mail',
            'Library Mail',
            'Express Mail',
            'Express Mail PO to PO' ,
            'Express Mail Flat Rate Envelope' ,
            'Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee',
            'Express Mail Sunday/Holiday Guarantee' ,
            'Express Mail Flat Rate Envelope Hold For Pickup' ,
            'Express Mail Hold For Pickup',
            'Global Express Guaranteed (GXG)' ,
            'Global Express Guaranteed Non-Document Rectangular',
            'Global Express Guaranteed Non-Document Non-Rectangular',
            'USPS GXG Envelopes',
            'Express Mail International',
            'Express Mail International Flat Rate Envelope' ,
            'Priority Mail',
            'Priority Mail Small Flat Rate Box',
            'Priority Mail Medium Flat Rate Box' ,
            'Priority Mail Large Flat Rate Box',
            'Priority Mail Flat Rate Box',
            'Priority Mail Flat Rate Envelope' ,
            'Priority Mail International',
            'Priority Mail International Flat Rate Envelope' ,
            'Priority Mail International Small Flat Rate Box',
            'Priority Mail International Medium Flat Rate Box' ,
            'Priority Mail International Large Flat Rate Box',
            'Priority Mail International Flat Rate Box'
        );
        return array_combine($_labels, $_labels);
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getConfigOptionName($id)
    {
        $options = self::getConfigOptionsArray();
        $return = array();

        foreach(explode(Axis_Config::MULTI_SEPARATOR, $id) as $key) {
            if (array_key_exists($key, $options)) {
                $return[$key] = $options[$key];
            }
        }
        if (count($return) === count($options)) {
            return 'All';
        }
        return implode(", ", $return);
    }

    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionDeafultValue()
    {
        return implode(
            Axis_Config::MULTI_SEPARATOR, 
            array_keys(self::getConfigOptionsArray())
        );
    }
}