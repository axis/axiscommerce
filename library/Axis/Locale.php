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
     * Retrieve languageId from session;
     *
     * @static
     * @return int
     */
    public static function getLanguageId()
    {
        return self::getSessionStorage()->language;
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
     * Retrieve first suitable locale with language
     *
     * @static
     * @param string $language Language ISO code
     * @return string Locale ISO code
     */
    private static function _getLocaleFromLanguageCode($language)
    {
        if (!empty($language)) {
            $locales = Axis::single('locale/option_locale')->toArray();
            foreach ($locales as $locale) {
                list($_language) = explode('_', $locale);
                if ($_language === $language) {
                    return $locale;
                }
            }
        }

        return self::DEFAULT_LOCALE;
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
