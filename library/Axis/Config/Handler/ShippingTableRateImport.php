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
class Axis_Config_Handler_ShippingTableRateImport implements Axis_Config_Handler_Interface
{
    /**
     *
     * @param mixed $value
     * @return mixed
     */
    public static function getSaveValue($value)
    {
        if (!is_array($value)) {
           return $value;
        }

        function remove_quotes(&$str)
        {
             $str = str_replace(array('"', "'"), '', $str);
        }

        $filename = Axis::config()->system->path . '/var/export/' . current($value);

        if (@!$fp = fopen($filename, 'r')) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    "Can't open file : %s", $filename
            ));
            return current($value);
        }

        $titles = fgetcsv($fp, 2048, ',', "'");
        array_walk($titles, 'remove_quotes');
        $rowSize = count($titles);

        Axis::table('shippingtable_rate')->delete(
            "site_id = " . $value['siteId']
        );
        while (!feof($fp)) {
            $data = fgetcsv($fp, 2048, ',', "'");
            if (!is_array($data)) {
                continue;
            }
            $data = array_pad($data, $rowSize, '');
            array_walk($data, 'remove_quotes');
            $data = array_combine($titles, $data);

            Axis::table('shippingtable_rate')->insert(array(
                'site_id' => $value['siteId'],
                'country_id' => Axis::single('location/country')
                    ->getIdByIsoCode3($data['Country']),
                'zone_id' => Axis::single('location/zone')
                    ->getIdByCode($data['Region/State']),
                'zip' => $data['Zip'],
                'value' => $data['Value'],
                'price' => $data['Price']

           ));
        }
        return current($value);
    }

    /**
     *
     * @static
     * @param array $value
     * @param Zend_View_Interface $view
     * @return string
     */
    public static function getHtml($value, Zend_View_Interface $view = null)
    {
        $html = $view->formText('confValue', $value);
        return $html;
    }

    /**
     *
     * @static
     * @param mixed $value
     * @return mixed
     */
    public static function getConfig($value)
    {
        return $value;
    }
}
