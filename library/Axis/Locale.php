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
 * @copyright   Copyright 2008-2012 Axis
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
    const DEFAULT_LOCALE = 'en_US';

    /**
     *
     * @return Zend_Locale
     */
    public static function getLocale()
    {
        if (!Zend_Registry::isRegistered('Zend_Locale')) {
            $instance = new Zend_Locale(self::DEFAULT_LOCALE);
            Zend_Locale::setCache(Axis::cache());
            Zend_Registry::set('Zend_Locale', $instance);
        }

        return Zend_Registry::get('Zend_Locale');
    }

    /**
     *
     * @return Zend_Session_Namespace
     */
    public static function getSessionStorage()
    {
        if (Axis_Area::isInstaller()) {
            $session = Axis::session('install');
        } else {
            $session = Axis::session();
        }
        return $session;
    }

    /**
     * Set locale and language
     *
     * @param string $locale
     * @return boolean
     */
    public static function setLocale($locale)
    {
        $instance = self::getLocale();

        if ($locale === $instance->toString()) {
            return true;
        }

        if (!$instance->isLocale($locale)) {
            return false;
        }

        $session = self::getSessionStorage();

        $instance->setLocale($locale);
        $session->locale = $locale;

        self::_initLanguageIdFromLocale();

        return true;
    }

    public static function _initLanguageIdFromLocale()
    {
        $languages = Axis::single('locale/language')
            ->select(array('locale', 'id'))
            ->fetchPairs();

        $locale = self::getLocale()->toString();
        if (isset($languages[$locale])) {
            $language = $languages[$locale];
        } else {
            $language = Axis::config('locale/main/language_' . Axis_Area::getArea());
        }

        if (!array_search($language, $languages)) {
            $language = current($languages);
        }

        $session = self::getSessionStorage();
        $session->language = $language;
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
        if (!empty($code)) {
            $localeList = self::getLocaleList(true);
            foreach ($localeList as $locale) {
                if (strpos($locale, $code) === 0) {
                    return $locale;
                }
            }
        }

        return self::DEFAULT_LOCALE;
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
     * @param $installedOnly bool
     * @static
     * @return array
     */
    public static function getLocaleList($installedOnly = false)
    {
        if (!$installedOnly) {
            return array_keys(Zend_Locale::getLocaleList());
        }
        return Axis::single('locale/option_locale')->toArray();
    }

    /**
     * @static
     * @return array
     */
    public static function getInstallLocaleList()
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
     * Retrieve languageId from session;
     *
     * @static
     * @return int
     */
    public static function getLanguageId()
    {
        return Axis::session()->language;
    }

    /**
     * Retrieve part of url, responsible for locale
     *
     * @static
     * @param string $locale Locale ISO code
     * @return string Part of url ('/uk')
     */
    public static function getLanguageUrl($locale = null)
    {
        if (null !== $locale) {
            list($language) = explode('_', $locale);
        } else {
            $language = self::getLocale()->getLanguage();
            $locale   = self::getLocale()->toString();
        }

        if ($locale == self::getDefaultLocale()) {
            return '';
        }
        if ($locale == self::_getLocaleFromLanguageCode($language)) {
            return '/' . $language;
        }

        return '/' . $locale;
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
     * Returns number from string
     *
     * @param string $value
     * @return float
     */
    public static function getNumber($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            return floatval($value);
        }

        $value = str_replace('\'', '', $value);
        $value = str_replace(' ', '', $value);
        $value = str_replace(',', '.', $value);

        return floatval($value);
    }
}
