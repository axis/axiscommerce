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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Locale
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale
{
    const DEFAULT_LOCALE    = 'en_US';
    const DEFAULT_CURRENCY  = 'USD';
    const DEFAULT_TIMEZONE  = 'America/Los_Angeles';

    /**
     * Set locale and language if possible
     *
     * @static
     * @param string (locale or language) $locale
     */
    public static function setLocale($locale = 'auto')
    {
        $nsMain = Axis::session();

        if (!strstr($locale, '_')) {
            $locale = self::_getLocaleFromLanguageCode($locale);
        }

        if (Zend_Registry::isRegistered('Zend_Locale')) {
            $currentLocale = Zend_Registry::get('Zend_Locale');
            $currentLocale->setLocale($locale);
        } else {
            try {
                $currentLocale = new Zend_Locale($locale);
            } catch (Zend_Locale_Exception $e) {
                $currentLocale = new Zend_Locale(self::DEFAULT_LOCALE);
            }
            Zend_Locale::setCache(Axis::cache());
            Zend_Registry::set('Zend_Locale', $currentLocale);
        }

        $availableLanguages = Axis::single('locale/language')
            ->select(array('locale', 'id'))
            ->fetchPairs();

        if (Zend_Registry::isRegistered('area')
            && Zend_Registry::get('area') == 'admin') {

            $nsMain->locale = $locale;
            $defaultLanguage = Axis::config('locale/main/language_admin');
            if (array_search($defaultLanguage, $availableLanguages)) {
                $nsMain->language = $defaultLanguage;
            } else {
                $nsMain->language = current($availableLanguages);
            }
        } else {
            $localeCode = $currentLocale->toString();
            if (isset($availableLanguages[$localeCode])) {
                $nsMain->language = $availableLanguages[$localeCode];
            } else {
                $defaultLanguage = Axis::config('locale/main/language_front');
                if (array_search($defaultLanguage, $availableLanguages)) {
                    $nsMain->language = $defaultLanguage;
                } else {
                    $nsMain->language = current($availableLanguages);
                }
            }
        }

        self::setTimezone();
    }

    /**
     * Retrieve first suitable locale with language
     *
     * @static
     * @param string $code Language ISO code
     * @return string Locale ISO code
     */
    private static function _getLocaleFromLanguageCode($code)
    {
        $localeList = self::getLocaleList(true);

        foreach ($localeList as $locale) {
            if (strstr($locale, $code)) {
                return $locale;
            }
        }

        return self::DEFAULT_LOCALE;
    }

    /**
     * Retrieve locale object
     *
     * @static
     * @return Zend_Locale
     */
    public static function getLocale()
    {
        if (!Zend_Registry::isRegistered('Zend_Locale')) {
            if (Zend_Registry::get('area') == 'front'
                && Axis_Controller_Router_Route::hasLocaleInUrl()) {

                self::setLocale(Axis_Controller_Router_Route::getCurrentLocale());
            } elseif (Zend_Registry::get('area') == 'admin'
                && isset(Axis::session()->locale)) {

                self::setLocale(Axis::session()->locale);
            } else {
                self::setLocale(Axis::config('locale/main/locale'));
            }
        }
        return Zend_Registry::get('Zend_Locale');
    }

    /**
     * Retrieve default locale from config
     *
     * @static
     * @return string Locale ISO code
     */
    public static function getDefaultLocale()
    {
        return Axis::config('locale/main/locale');
    }

    /**
     * Returns a list of all known locales, or all installed locales
     *
     * @static
     * @return array
     */
    public static function getLocaleList($installedOnly = false)
    {
        if (!$installedOnly) {
            return array_keys(Zend_Locale::getLocaleList());
        }
        return Axis::single('locale/language')->getLocaleList();
    }

    /**
     * Retrieve languageId from session;
     *
     * @static
     * @return int
     */
    public static function getLanguageId()
    {
        if (!isset(Axis::session()->language)) {
            Axis::session()->language = Axis::config()->locale->main->language;
        }
        return Axis::session()->language;
    }

    /**
     * Retrieve part of url, responsible for locale
     *
     * @static
     * @return string Part of url ('/uk')
     */
    public static function getLanguageUrl()
    {

        $language = self::getLocale()->getLanguage();
        $locale = self::getLocale()->toString();

        if ($locale == self::getDefaultLocale()) {
            return '';
        }
        if ($locale == self::_getLocaleFromLanguageCode($language)) {
            return '/' . $language;
        }

        return '/' . $locale;
    }

    /**
     * get default store timezone
     *
     * @static
     * @return  string  example : "Australia/Hobart"
     */
    public static function getDefaultTimezone()
    {
        return Axis::config('locale/main/timezone');
    }

    /**
     * get timezone
     *
     * @static
     * @return string
     */
    public static function getTimezone()
    {
        return date_default_timezone_get();
    }

    /**
     * set timezone
     *
     * @static
     * @param mixed void|string
     * @return bool
     */
    public static function setTimezone($timezone = null)
    {
        if (null === $timezone) {
            $timezone = Axis::config('locale/main/timezone');
        }
        if (@date_default_timezone_set($timezone)) {
            return true;
        }
        return @date_default_timezone_set(self::DEFAULT_TIMEZONE);
    }

    /**
     * Retrieve the list of available admin intrerface tranlations
     *
     * @static
     * @return array
     */
    public static function getAdminLocales()
    {
        if (!$locales = Axis::cache()->load('locales_list')) {
            $path = Axis::config()->system->path . '/app/locale';

            try {
                $locales_dir = new DirectoryIterator($path);
            } catch (Exception $e) {
                throw new Axis_Exception("Directory $path not readable");
            }

            $locale = Axis_Locale::getLocale();
            $locales = array();

            foreach ($locales_dir as $localeDir) {
                $localeCode = $localeDir->getFilename();
                if ($localeCode[0] == '.' || !strstr($localeCode, '_')) {
                    continue;
                }
                list($language, $country) = explode('_', $localeCode, 2);

                $language = $locale->getTranslation($language, 'language', $localeCode);
                $country = $locale->getTranslation($country, 'country', $localeCode);
                if (!$language) {
                    $language = $locale->getTranslation(
                        $language, 'language', Axis_Locale::DEFAULT_LOCALE
                    );
                }
                if (!$country) {
                    $country = $locale->getTranslation(
                        $country, 'country', Axis_Locale::DEFAULT_LOCALE
                    );
                }
                $locales[$localeCode] = ucfirst($language) . ' (' . $country . ')';
            }
            ksort($locales);
            Axis::cache()->save($locales, 'locales_list', array('locales'));
        }
        return $locales;
    }
}