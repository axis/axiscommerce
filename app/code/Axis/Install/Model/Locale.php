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
 * @package     Axis_Install
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Install
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Install_Model_Locale
{
    const DEFAULT_LOCALE = 'en_US';

    /**
     * @static
     * @param string (locale or language) $locale
     */
    public static function setLocale($locale = 'en_US')
    {
        $session = Zend_Registry::get('session');

        if (Zend_Registry::isRegistered('Zend_Locale')) {
            $currentLocale = Zend_Registry::get('Zend_Locale');
        } else {
            $currentLocale = new Zend_Locale();
            Zend_Registry::set('Zend_Locale', $currentLocale);
        }

        if ($currentLocale->isLocale($locale)) {
            $currentLocale->setLocale($locale);
        }
        $session->current_locale = $locale;
    }

    /**
     * @return Zend_Locale
     */
    public static function getLocale()
    {
        $session = Zend_Registry::get('session');

        if (isset($session->current_locale)) {
            self::setLocale($session->current_locale);
        } else {
            self::setLocale(self::DEFAULT_LOCALE);
        }

        return Zend_Registry::get('Zend_Locale');
    }

    /**
     * Retrieve array of available translations
     *
     * @return array
     */
    public static function getAvailableLocales()
    {
        $path = AXIS_ROOT . '/app/locale/';

        try {
            $localeDir = new DirectoryIterator($path);
        } catch (Exception $e) {
            throw new Axis_Exception("Directory $path not readable");
        }

        $currentLocale = self::getLocale();
        $locales = array();

        foreach ($localeDir as $locale) {
            if ($locale->isDot() || !$locale->isDir()) {
                continue;
            }
            $localeName = $locale->getFilename();
            list($language, $country) = explode('_', $localeName, 2);

            $language = $currentLocale->getTranslation($language, 'language', $localeName);
            $country = $currentLocale->getTranslation($country, 'country', $localeName);
            if (!$language) {
                $language = $currentLocale->getTranslation($language, 'language', 'en_US');
            }
            if (!$country) {
                $country = $currentLocale->getTranslation($country, 'country', 'en_US');
            }
            $locales[$localeName] = ucfirst($language) . ' (' . $country . ')';
        }
        ksort($locales);

        return $locales;
    }

    /**
     * @static
     * @return array
     */
    public static function getLocaleList()
    {
        $options = array();

        $locales = Zend_Locale::getLocaleList();
        $languages = Zend_Locale::getTranslationList('language', self::getLocale());
        $countries = Zend_Locale::getTranslationList('territory', self::getLocale(), 2);

        foreach ($locales as $code => $is_active) {
            if (strstr($code, '_')) {
                $data = explode('_', $code);
                if (!isset($languages[$data[0]]) || !isset($countries[$data[1]])) {
                    continue;
                }
                $options[$code] = ucfirst($languages[$data[0]]) . ' (' . $countries[$data[1]] . ')';
            }
        }
        return $options;
    }

    /**
     *
     * @static
     * @return array
     */
    public static function getTimeZoneList()
    {
        $options = array();
        $zones = Zend_Locale::getTranslationList('WindowsToTimezone', self::getLocale());

        asort($zones);
        foreach ($zones as $code => $name) {
            $name = trim($name);
            $options[$code] = empty($name) ? $code : $name . ' (' . $code . ')';
        }
        return $options;
    }

    /**
     * @static
     * @return array
     */
    public static function getCurrencyList()
    {
        return Zend_Locale::getTranslationList('NameToCurrency', self::getLocale());

    }
}