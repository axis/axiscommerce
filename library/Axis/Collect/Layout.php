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
 * @package     Axis_Layout
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Layout
 * @subpackage  Collect
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Collect_Layout implements Axis_Collect_Interface
{
    /**
     *
     * @static
     * @var array
     */
    protected static $_collection;

    /**
     *
     * @static
     * @return array
     */
    public static function collect()
    {
        if (null === self::$_collection) {
            $designPath = Axis::config()->system->path . '/app/design/front';
            $skins = Axis_Collect_Skin::collect();
            $layouts = array();
            foreach ($skins as $skin) {
                $path = $designPath . '/' . $skin . '/layouts';
                if (!file_exists($path)) {
                    continue;
                }
                $dh = opendir($path);
                while (($file = readdir($dh))) {
                    if (is_dir($path . '/' . $file) 
                        || substr($file, 0, 7) != 'layout_') {

                        continue;
                    }
                    $layout =  $skin . '_' . substr($file, 7, -6);
                    $layouts[$layout] = $layout;
                }
                closedir($dh);
            }
            self::$_collection = $layouts;
        }
        return self::$_collection;
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getName($id)
    {
        if (null === self::$_collection) {
            self::$_collection = self::collect();
        }
        return isset(self::$_collection[$id]) ? self::$_collection[$id] : false;
    }
}