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
 * @subpackage  Axis_Catalog_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Option_Lightzoom_StagePosition extends Axis_Config_Option_Array_Abstract
{   
    const LEFT  = 'left';
    const RIGHT = 'right';

    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return array(
            self::LEFT  => ucfirst(self::LEFT),
            self::RIGHT => ucfirst(self::RIGHT)
        );
    }
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getDeafult()
    {
        return self::RIGHT;
    }
}