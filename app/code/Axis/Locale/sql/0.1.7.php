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
 * @package     Axis_Locale
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Locale_Upgrade_0_1_7 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.7';

    public function up()
    {
        $models = array(
            'Currency'     => 'Axis_Locale_Model_Option_Currency',
            'Language'     => 'Axis_Locale_Model_Option_Language',
            'ZendCountry'  => 'Axis_Locale_Model_Option_ZendCountry',
            'ZendCurrency' => 'Axis_Locale_Model_Option_ZendCurrency',
            'ZendLocale'   => 'Axis_Locale_Model_Option_ZendLocale',
            'ZendTimezone' => 'Axis_Locale_Model_Option_ZendTimezone'
            
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($models[$row->model])) {
                $row->model = $models[$row->model];
                $row->save();
            }
        }
        
        $row = Axis::single('core/config_field')->select()
            ->where('path = ?', 'locale/main/currency')
            ->fetchRow();
        
        $row->type = 'select';
        $row->model = 'Axis_Locale_Model_Option_Currency_Default';
        $row->save();
        
    }
}