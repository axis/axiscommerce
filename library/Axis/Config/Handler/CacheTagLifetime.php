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
 * @package     Axis_Config
 * @subpackage  Axis_Config_Handler
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Config
 * @subpackage  Axis_Config_Handler
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Config_Handler_CacheTagLifetime implements  Axis_Config_Handler_Interface
{
    /**
     *
     * @param void
     * @return array
     */
    public static function _getOptions()
    {
        $tags = array();
        foreach (Axis_Collect_CacheTag::collect() as $tag) {
            $tags[$tag] = array('name' => $tag, 'id' => $tag);
        }
        return $tags;
    }

    /**
     *
     * @static
     * @param array $params
     * @return string
     */
    public static function getSaveValue($params)
    {
        return Zend_Json_Encoder::encode($params);
    }

    /**
     *
     * @static
     * @param string $value
     * @param Zend_View_Interface $view
     * @return string
     */
    public static function getHtml($value, Zend_View_Interface $view = null)
    {
        $value = Zend_Json_Decoder::decode($value);
        $html = '';
        foreach (self::_getOptions() as $options)
        {
            $html .= $options['name'] . ' ' . $view->formText(
                'confValue[' . $options['name']. ']',
                $value[$options['id']],
                array('size' => '50')
            );
        }
        return $html;
    }

    /**
     * @static
     * @param string $value
     * @return string
     */
    public static function getConfig($value)
    {
        return Zend_Json_Decoder::decode($value);
    }
}
