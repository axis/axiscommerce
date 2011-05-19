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
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Address
{
    protected $_addressFormats = array();

    public function __construct()
    {
        $this->_addressFormats = Axis::model('location/address_format')
            ->select()
            ->fetchAssoc();
    }

    /**
     *
     * @param Axis_Address $address
     * @param string $EOL
     * @return string
     */
    public function address(Axis_Address $address, $EOL = '<br/>')
    {
//        $template = '{{firstname}} {{lastname}}EOL' .
//        '{{if company}}{{company}}EOL{{/if}}' .
//        '{{street_address}}EOL' .
//        '{{if suburb}}{{suburb}}EOL{{/if}}'.
//        '{{city}} {{if zone.name}}{{zone.name}} {{/if}}{{postcode}}EOL' .
//        '{{country.name}}EOL' .
//        'T: {{phone}}EOL' .
//        '{{if fax}}F: {{fax}}EOL{{/if}}'
//        ;
        $address = $address->toArray();
        $addressFormatId = !empty($address['address_format_id']) ?
            $address['address_format_id'] :
                Axis::config('locale/main/addressFormat');

        if (empty($this->_addressFormats[$addressFormatId])) {
            throw new Axis_Exception(
                Axis::translate('location')->__(
                    'Not correct address format id'
            ));
        }
        $template = $this->_addressFormats[$addressFormatId]['address_format'];

        if (isset($address['zone']['id'])
            && 0 == $address['zone']['id']) {

            unset($address['zone']);
        }

        $matches = array();
        preg_match_all('/{{if (.+)(?:\.(.+))?}}(.+){{\/if}}/U', $template, $matches);
        foreach ($matches[0] as $key => $condition) {
            $replaced = empty($matches[2][$key]) ?
                (empty($address[$matches[1][$key]]) ? '' : $matches[3][$key]) :
                (empty($address[$matches[1][$key]][$matches[2][$key]]) ?
                    '' : $matches[3][$key]);

            $template = str_replace($condition, $replaced, $template);
        }

        preg_match_all('/{{(.+)(?:\.(.+))?}}/U', $template, $matches);
        foreach ($matches[0] as $key => $condition) {
           $replaced = empty($matches[2][$key]) ?
               $address[$matches[1][$key]] :
                   $address[$matches[1][$key]][$matches[2][$key]];

           $template = str_replace($condition, $this->view->escape($replaced), $template);
        }

        return str_replace('EOL', $EOL, $template);
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}