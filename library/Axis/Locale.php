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
     * Set locale and language
     *
     * @param string $locale
     * @return boolean
     */
    public static function setLocale($locale)
    {
        $instance = Axis::locale();

        if ($locale === $instance->toString()) {
            return true;
        }

        if (!self::isValid($locale)) {
            return false;
        }

        $instance->setLocale($locale);
        Axis::session()->locale = $locale;

        return true;
    }

    /**
     *
     * @param string $locale
     * @return boolean
     */
    public static function isValid($locale)
    {
        if (!Zend_Locale::isLocale($locale)) {
            return false;
        }

        if (Axis_Area::isFrontend()) {
            $locales = Axis::single('locale/option_locale')->toArray();
        } else {
            $locales = array_keys(
                Axis::single('locale/option_filesystem_locale')->toArray()
            );
        }
        if (!in_array($locale, $locales)) {
            return false;
        }
        return true;
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
     * @deprecated
     * @static
     * @param string $locale Locale ISO code
     * @return string Part of url ('/uk')
     */
    public static function getLanguageUrl($locale = null)
    {
        if (null === $locale) {
            $locale = Axis::locale()->toString();
        }

        if (!Zend_Locale::isLocale($locale)) {
            return '';
        }

        $locales = Axis::single('locale/option_locale')->toArray();
        if (!in_array($locale, $locales)) {
            return '';
        }

        if ($locale == Axis::config('locale/main/locale')) {
            return '';
        }
        list($language) = explode('_', $locale);

        foreach ($locales as $_locale) {
            if (strpos($_locale, $language) === 0) {
                break;
            }
        }
        if ($locale !== $_locale) {
            return '/' . $locale;
        }
        return '/' . $language;
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
