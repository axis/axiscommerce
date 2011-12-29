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
 * @package     Axis_GoogleBase
 * @subpackage  Axis_GoogleBase_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_GoogleBase
 * @subpackage  Axis_GoogleBase_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_GoogleBase_Model_Payment implements Axis_Config_Option_Array_Interface
{   
    //@todo ? int value
    const DISCOVER         = 'Discover';
    const AMERICAN_EXPRESS = 'American Express';
    const VISA             = 'VISA';
    const MASTER_CARD      = 'MasterCard';
    const WIRE_TRANSFER    = 'Wire transfer';
    const CHECK            = 'Check';
    const CASH             = 'Cash';
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        return array(
            self::DISCOVER         => self::DISCOVER,
            self::AMERICAN_EXPRESS => self::AMERICAN_EXPRESS,
            self::VISA             => self::VISA,
            self::MASTER_CARD      => self::MASTER_CARD,
            self::WIRE_TRANSFER    => self::WIRE_TRANSFER,
            self::CHECK            => self::CHECK,
            self::CASH             => self::CASH
        );
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