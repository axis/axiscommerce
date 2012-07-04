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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Collect
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_Model_Option_Filesystem_Locale extends Axis_Config_Option_Array_Abstract
{
    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        $options = array();

        if (!$options = Axis::cache()->load('locales_list')) {

            $path = Axis::config()->system->path . '/app/locale/';
            try {
                $dir = new DirectoryIterator($path);
            } catch (Exception $e) {
                throw new Axis_Exception("Directory $path not readable");
            }

            foreach ($dir as $_dir) {
                if ($_dir->isDot() || !$_dir->isDir()) {
                    continue;
                }
                $locale = $_dir->getFilename();
                list($language, $country) = explode('_', $locale, 2);

                $language = Zend_Locale::getTranslation(
                    $language, 'language', $locale
                );
                if (!$language) {
                    $language = Zend_Locale::getTranslation(
                        $language, 'language', Axis_Locale::DEFAULT_LOCALE
                    );
                }

                $country = Zend_Locale::getTranslation(
                    $country, 'country', $locale
                );
                if (!$country) {
                    $country = Zend_Locale::getTranslation(
                        $country, 'country', Axis_Locale::DEFAULT_LOCALE
                    );
                }
                $options[$locale] = ucfirst($language) . ' (' . $country . ')';
            }
            ksort($options);
            Axis::cache()->save($options, 'locales_list', array('locales'));
        }
        return $options;
    }
}
