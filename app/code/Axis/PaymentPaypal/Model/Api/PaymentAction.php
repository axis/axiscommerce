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
 * @package     Axis_PaymentPaypal
 * @subpackage  Axis_PaymentPaypal_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_PaymentPaypal
 * @subpackage  Axis_PaymentPaypal_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_PaymentPaypal_Model_Api_PaymentAction implements Axis_Config_Option_Interface
{
    const AUTHORIZATION = 'Authorization';
    const SALE          = 'Sale';
    
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        return array(
            self::AUTHORIZATION => self::AUTHORIZATION,
            self::SALE          => self::SALE
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
        return isset($options[$id]) ? $options[$id] : '';
    }
   
}