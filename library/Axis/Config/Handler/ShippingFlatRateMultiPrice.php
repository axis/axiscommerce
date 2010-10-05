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
 * @package     Axis_ShippingFlat
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_ShippingFlat
 * @subpackage  Handler
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Config_Handler_ShippingFlatRateMultiPrice implements  Axis_Config_Handler_Interface
{
    /**
     *
     * @static
     * @param void
     * @return array
     */
    public static function getSaveValue($params)
    {
        if (is_array($params)) {
            $temp = array();
            foreach ($params as $param) {
                $temp[$param['subcode']] = array(
                    'title' =>  $param['title'],
                    'price' => $param['price']
                );
            }
            $params = $temp;
        }
        if (is_string($params)) {
            $params = array('standard' => array('title' => 'Standard', 'price' => $params));
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
        $value = json_decode($value, true);
        if (!is_array($value)) {
            $value = json_decode(self::getSaveValue('25'), true);
        }
        $html = '';
        $i = 1;
        foreach ($value as $subcode => $item) {
            $html .=
                  '<div id="wraper-' . $i . '">'
                  . 'Subcode : ' . $view->formText('confValue[' . $i . '][subcode]', $subcode, array('size' => '10'))
                  . 'Title : ' . $view->formText('confValue[' . $i . '][title]', $item['title'], array('size' => '10'))
                  . 'Price : ' . $view->formText('confValue[' . $i . '][price]', $item['price'], array('size' => '10'))
                  . $view->formButton('shippingmultirate-template-remove', 'Remove',
                        array('onclick' => 'function remove() {$(\'#wraper-' . $i . '\').remove();}remove();')
                    )
                  . '</div>';
            $i++;
        }
        
        $jsStrReplaceFunction =
        ' function str_replace(search, replace, subject, count) {

            var i = 0, j = 0, temp = \'\', repl = \'\', sl = 0, fl = 0,
                    f = [].concat(search),
                    r = [].concat(replace),
                    s = subject,
                    ra = r instanceof Array, sa = s instanceof Array;
            s = [].concat(s);
            if (count) {
                this.window[count] = 0;
            }

            for (i=0, sl=s.length; i < sl; i++) {
                if (s[i] === \'\') {
                    continue;
                }
                for (j=0, fl=f.length; j < fl; j++) {
                    temp = s[i]+\'\';
                    repl = ra ? (r[j] !== undefined ? r[j] : \'\') : r[0];
                    s[i] = (temp).split(f[j]).join(repl);
                    if (count && s[i] !== temp) {
                        this.window[count] += (temp.length-s[i].length)/f[j].length;}
                }
            }
            return sa ? s : s[0];
        } ';

        $html .= '<div id="shippingmultirate-template" style="display:none" >'
            . '<div id="wraper-{template}">'
            . 'Subcode : ' . $view->formText('{template}[subcode]', $subcode, array('size' => '10'))
            . 'Title : ' . $view->formText('{template}[title]', $item['title'], array('size' => '10'))
            . 'Price : ' . $view->formText('{template}[price]', $item['price'], array('size' => '10'))
            . $view->formButton('shippingmultirate-template-remove', 'Remove',
                  array('onclick' => 'function remove() {$(\'#wraper-{template}\').remove();}remove();')
              )
            . '</div>'
            . '</div>'
           
            . $view->formButton('shippingmultirate-template-add', 'Add', array('onclick' => ' var i = ' . $i . ';'
                . $jsStrReplaceFunction . '
                function clone() {
                    i = i + 1;
                    var rand = Math.floor(Math.random() * (2147483647 - 0 + 1)) + 0;
                    var html = $(\'#shippingmultirate-template\').html();
                    html = str_replace(\'{template}\', \'confValue[\' + rand + \']\', html);
                    $(\'#shippingmultirate-template\').before(html);
                    //$(\'#shippingmultirate-template-add\').hide();
                } clone();'))
            ;

        return $html;
    }

    /**
     *
     * @static
     * @param string $value
     * @return string
     */
    public static function getConfig($value)
    {
        return json_decode($value, true);
    }
}
