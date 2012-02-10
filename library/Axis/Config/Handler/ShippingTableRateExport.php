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
class Axis_Config_Handler_ShippingTableRateExport implements Axis_Config_Handler_Interface
{
    /**
     *
     * @static
     * @param array $value
     * @return mixed
     */
    public static function prepareConfigOptionValue($value)
    {
        if (!is_array($value)) {
           return $value;
        }

        $filename = Axis::config()->system->path
            . '/var/export/' . current($value);

        if (@!$fp = fopen($filename, 'w')) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    "Can't write in file: %s", $filename
                )
            );
            return current($value);
        }

        $titles = explode(',', 'Country,Region/State,Zip,Value,Price');
        fputcsv($fp, $titles, ',', "'");
        foreach (Axis::table('shippingtable_rate')->fetchAll() as $row) {
            fputcsv($fp, array(
                Axis::single('location/country')
                    ->getIsoCode3ById($row->country_id),
                Axis::single('location/zone')
                    ->getCodeById($row->zone_id),
                $row->zip,
                $row->value,
                $row->price
            ), ',', "'");
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
        return $view->formText('confValue', $value);
    }

    /**
     *
     * @static
     * @param string $value
     * @return string
     */
    public static function getConfigOptionValue($value)
    {
        return $value;
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
