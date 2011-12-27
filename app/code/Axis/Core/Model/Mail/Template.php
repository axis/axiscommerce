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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Mail_Template implements Axis_Config_Option_Array_Interface
{
    /**
     *
     * @static
     * @var array
     */
    private static $_templates;

    /**
     *
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        if (null === self::$_templates) {
            $path = Axis::config()->system->path . '/app/design/mail';
            $templates = array();
            if (!file_exists($path))
                return false;
            $dh = opendir($path);

            while (($file = readdir($dh))) {

                if (!is_dir($path . '/' . $file) &&
                    substr($file, -11) == '_html.phtml' &&
                    is_file($path . '/' . substr($file, 0, -11) . '_txt.phtml')
                   )
                $templates[substr($file, 0, -11)] = substr($file, 0, -11);
            }

            closedir($dh);
            self::$_templates = $templates;
        }
        return self::$_templates;
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getConfigOptionName($id)
    {
        $templates = $this->collect();
        return $templates[$id];
    }
}