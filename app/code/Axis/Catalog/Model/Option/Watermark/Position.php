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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Option_Watermark_Position extends Axis_Config_Option_Array_Abstract
{
    const TOP    = 'top';
    const MIDDLE = 'middle';
    const BOTTOM = 'bottom';
    
    const LEFT   = 'left';
    const CENTER = 'center';
    const RIGHT  = 'right';
    
//    const STRETCH = 'stretch';

    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        $options = array();
        foreach (array(self::TOP, self::MIDDLE, self::BOTTOM) as $h) {
            foreach (array(self::LEFT, self::CENTER, self::RIGHT) as $w) {
                
                $options[$h . '_' . $w] = ucfirst($h) . ' ' . ucfirst($w);
            }
        }
        
        return $options;
    }
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getDeafult()
    {
        return self::BOTTOM . '_' . self::RIGHT;
    }
}