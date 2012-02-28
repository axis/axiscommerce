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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Config
 * @subpackage  Axis_Config_Handler
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Config_Handler_TestJson implements  Axis_Config_Handler_Interface
{
    /**
     *
     * @static
     * @param void
     * @return const array
     */
    public static function _getOptions()
    {
        return array(
            'id'      => array('name'=> 'id',   'type' => '',         'id' => 'id'),
            'is_used' => array('name'=> 'used', 'type' => 'bool',     'id' => 'used'),
            'cost'    => array('name'=> 'cost', 'type' => 'text',     'id' => 'cost'),
            'qty'     => array('name'=> 'qty',  'type' => 'select',   'id' => 'qty',  'config_options' => array(1 => 'ses', 2 => 'sos')),
            'card'    => array('name'=> 'card', 'type' => 'multiple', 'id' => 'card', 'config_options' => array('visa' => 'Visa'  , 'amex' => 'Amex'))
        );
    }

    /**
     *
     * @static
     * @param array $params
     * @return string
     */
    public static function encodeConfigOptionValue($params)
    {
        return json_encode($params);
    }

    /**
     *
     * @param array $value
     * @param Zend_View_Interface $view
     * @return string
     */
    public static function getHtml($value, Zend_View_Interface $view = null)
    {

        $value = json_decode($value, true);
        foreach (self::_getOptions() as $options)
        {
            switch ($options['type']) {
                case 'bool':
                    $html .= $view->formRadio(
                        'confValue[' . $options['name']. ']',
                        $value[$options['id']], null,
                        array(
                            '1' => Axis::translate()->__('Yes'),
                            '0' => Axis::translate()->__('No')
                        )
                    );
                    break;
                case 'select':
                    $html .= $view->formSelect('confValue[' . $options['name']. ']',
                        $value[$options['id']], null,
                        $options['config_options']
                    );
                    break;
                case 'multiple':
                    $html .= '<br />';
                    foreach ($options['config_options'] as $key => $dataItem) {
                        $html .= $view->formCheckbox(
                            'confValue[' . $options['name']. '][' . $key . ']',
                            isset($value[$options['name']][$key]) && ($value[$options['name']][$key]) ? 1 : null,
                            null,
                            array(1, 0)
                        ) . " $dataItem <br /> ";

                    }
                    break;
                case 'text':
                    $html .= $view->formTextarea('confValue[' . $options['name']. ']', $value[$options['id']], array('rows' => 8, 'cols' => 45));
                    break;

                default:
                    $html .= $view->formText('confValue[' . $options['name']. ']', $value[$options['id']], array('size' => '50'));
            }
        }
        return $html;
    }

    /**
     *
     * @static
     * @param string $value
     * @return array
     */
    public static function decodeConfigOptionValue($value)
    {
        return json_decode($value, true);
    }
    
    /**
     *
     * @static
     * @param int $id
     * @return string
     */
    public static function getConfigOptionName($id) 
    {
        return $id;
    }
}
