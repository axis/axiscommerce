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
class Axis_Locale_Model_ZendLocale implements Axis_Config_Option_Array_Interface
{
    /**
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        $options = array();
        $locale  = Axis_Locale::getLocale();

        $locales    = $locale->getLocaleList();
        $languages  = $locale->getTranslationList('language', $locale);
        $countries  = $locale->getTranslationList('territory', $locale, 2);

        if (!$languages || !$countries) {
            $languages  = $locale->getTranslationList(
                'language', Axis_Locale::DEFAULT_LOCALE
            );
            $countries  = $locale->getTranslationList(
                'territory', Axis_Locale::DEFAULT_LOCALE, 2
            );
        }

        foreach ($locales as $code => $isActive) {
            if (!strstr($code, '_')) {
                continue;
            }
            list($languageCode, $countryCode) = explode('_', $code);
            if (!isset($languages[$languageCode]) || !isset($countries[$countryCode])) {

                continue;
            }
            $options[$code] = ucfirst($languages[$languageCode]) . ' ('
                . $countries[$countryCode] . ')';
        }
        return $options;
    }

    /**
     *
     * @static
     * @param int $id
     * @return mixed string|void
     */
    public static function getConfigOptionName($id)
    {
        if (empty($id)) {
            return;
        }
        $data     = explode('_', $id);
        $locale   = Axis_Locale::getLocale();
        $language = $locale->getTranslation($data[0], 'language', $locale);
        $country  = $locale->getTranslation($data[1], 'country', $locale);

        return ucfirst($language) . ' (' . $country . ')';
    }
}