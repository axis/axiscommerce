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
class Axis_ShippingFedex_Model_Option_Standard_Package extends Axis_Config_Option_Array_Abstract
{
    const FEDEX_ENVELOPE = 'FEDEX_ENVELOPE';
    const FEDEX_PAK      = 'FEDEX_PAK';
    const FEDEX_BOX      = 'FEDEX_BOX';
    const FEDEX_TUBE     = 'FEDEX_TUBE';
    const FEDEX_10KG_BOX = 'FEDEX_10KG_BOX';
    const FEDEX_25KG_BOX = 'FEDEX_25KG_BOX';
    const YOUR_PACKAGING = 'YOUR_PACKAGING';
    
    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return array(
            self::FEDEX_ENVELOPE => 'FedEx Envelope',
            self::FEDEX_PAK      => 'FedEx Pak',
            self::FEDEX_BOX      => 'FedEx Box',
            self::FEDEX_TUBE     => 'FedEx Tube',
            self::FEDEX_10KG_BOX => 'FedEx 10kg Box',
            self::FEDEX_25KG_BOX => 'FedEx 25kg Box',
            self::YOUR_PACKAGING => 'Your Packaging'
        );
    }
}