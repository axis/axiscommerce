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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_HumanUri
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_HumanUri
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_HumanUri
{
    private static $_instance;

    /**
     * Retrieve singleton instance of Axis_HumanUri_Adapter
     *
     * @static
     * @return Axis_HumanUri_Adapter_Abstact
     */
    public static  function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = Axis_HumanUri::factory(
                Axis::config()->front->humanUrlAdapter
            );
        }

        return self::$_instance;
    }

    static function factory($adapterName)
    {
        if (!is_string($adapterName) or !strlen($adapterName)) {
            throw new Axis_HumanUri_Exception('Adapter name must be specified in a string.');
        }

        //$adapterName = strtolower($adapterName); // normalize input
        $adapterName = 'Axis_HumanUri_Adapter_' .
            str_replace(' ', '_' , ucwords(str_replace('_', ' ', $adapterName)));

        Zend_Loader::loadClass($adapterName);

        return new $adapterName();
    }

}