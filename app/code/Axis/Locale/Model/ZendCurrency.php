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
 * @package     Axis_Collect
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Collect
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_Model_ZendCurrency implements Axis_Config_Option_Array_Interface
{
    /**
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        $locale = Axis_Locale::getLocale();

        $currencies = $locale->getTranslationList('NameToCurrency', $locale);

        if (!$currencies) {
            $currencies = $locale->getTranslationList(
                'NameToCurrency', Axis_Locale::DEFAULT_LOCALE
            );
        }

        return $currencies;
    }

    /**
     *
     * @static
     * @param string $key
     * @return mixed string|void
     */
    public static function getConfigOptionValue($key)
    {
        if (empty($key)) {
            return;
        }
        $locale = Axis_Locale::getLocale();
        $name  = $locale->getTranslation($key, 'NameToCurrency', $locale);

        return empty($name) ? $key : $name . ' (' . $key . ')';
    }
}