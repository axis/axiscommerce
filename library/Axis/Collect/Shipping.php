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
 * @package     Axis_Shipping
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Shipping
 * @subpackage  Collect
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Collect_Shipping implements Axis_Collect_Interface
{
    /**
     *
     * @static
     * @return array
     */
    public static function collect()
    {
        $ret = array();
        foreach (Axis_Shipping::getMethods() as $methodCode => $method) {
            $ret[$methodCode] = $method->getTitle();
        }
        return $ret;
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getName($id)
    {
        if (!$id) {
            return '';
        }
        $collects = self::collect();
        if (strstr($id, ",")) {
            $ret = array();

            foreach(explode(",", $id) as $key) {
                if (array_key_exists($key, $collects))
                    $ret[$key] = $collects[$key];
            }
            if (count($ret) == count($collects)) {
                return 'All';
            }
            return implode(", ", $ret);
        }

        return $collects[$id];
    }
}