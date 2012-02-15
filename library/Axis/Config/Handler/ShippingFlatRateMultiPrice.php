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
class Axis_Config_Handler_ShippingFlatRateMultiPrice implements  Axis_Config_Handler_Interface
{
    /**
     *
     * @static
     * @param mixed
     * @return array
     */
    public static function encodeConfigOptionValue($params)
    {
        if (is_array($params)) {
            $temp = array();
            foreach ($params as $param) {
                $temp[$param['subcode']] = array(
                    'title' => $param['title'],
                    'price' => $param['price'],
                    'minOrderTotal' => $param['minOrderTotal'],
                    'maxOrderTotal' => $param['maxOrderTotal']
                );
            }
            $params = $temp;
        }
        if (is_string($params)) {
            $params = array(
                'standard' => array(
                    'title' => 'Standard',
                    'price' => $params,
                    'minOrderTotal' => '',
                    'maxOrderTotal' => ''
                )
            );
        }
        return json_encode($params);
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
        if (!is_array($value)) {
            $value = json_decode(self::encodeConfigOptionValue('25'), true);
        }
        $html = '<script type="text/javascript">
            function removeRate(id) {
                $(\'#wraper-\' + id).remove();
            }
            function addRate() {
                var rand = Math.floor(Math.random() * 2147483647);
                var html = $(\'#shippingmultirate-template\').html();
                html = html.replace(/{template_id}/g, rand)
                    .replace(/{template}/g, \'confValue[\' + rand + \']\');
                $(\'#shippingmultirate-template\').before(html);
            }
        </script>';
        $i = 1;
        foreach ($value as $subcode => $item) {
            $html .= '<div id="wraper-' . $i . '">'
                . 'Subcode : ' . $view->formText('confValue[' . $i . '][subcode]', $subcode, array('size' => '10'))
                . 'Title : ' . $view->formText('confValue[' . $i . '][title]', $item['title'], array('size' => '10'))
                . 'Price : ' . $view->formText('confValue[' . $i . '][price]', $item['price'], array('size' => '10'))
                . 'Min Subtotal : '
                    . $view->formText(
                        'confValue[' . $i . '][minOrderTotal]',
                        isset($item['minOrderTotal']) ? $item['minOrderTotal'] : '',
                        array('size' => '10')
                    )
                . 'Max Subtotal : '
                    . $view->formText(
                        'confValue[' . $i . '][maxOrderTotal]',
                        isset($item['maxOrderTotal']) ? $item['maxOrderTotal'] : '',
                        array('size' => '10')
                    )
                . $view->formButton('shippingmultirate-template-remove', 'Remove',
                    array('onclick' => 'removeRate(' . $i . ');')
                )
                . '</div>';
            $i++;
        }

        $html .= '<div id="shippingmultirate-template" style="display:none" >'
            . '<div id="wraper-{template_id}">'
            . 'Subcode : ' . $view->formText('{template}[subcode]', $subcode, array('size' => '10'))
            . 'Title : ' . $view->formText('{template}[title]', $item['title'], array('size' => '10'))
            . 'Price : ' . $view->formText('{template}[price]', $item['price'], array('size' => '10'))
            . 'Min Subtotal : '
                . $view->formText(
                    '{template}[minOrderTotal]',
                    isset($item['minOrderTotal']) ? $item['minOrderTotal'] : '',
                    array('size' => '10')
                )
            . 'Max Subtotal : '
                . $view->formText(
                    '{template}[maxOrderTotal]',
                    isset($item['maxOrderTotal']) ? $item['maxOrderTotal'] : '',
                    array('size' => '10')
                )
            . $view->formButton('shippingmultirate-template-remove', 'Remove',
                array('onclick' => 'removeRate(\'{template_id}\');')
            )
            . '</div>'
            . '</div>'

            . $view->formButton(
                'shippingmultirate-template-add',
                'Add',
                array('onclick' => 'addRate();')
            );

        return $html;
    }

    /**
     *
     * @static
     * @param string $value
     * @return string
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
