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
 * @package     Axis_Translate
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Translate
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Translate
{
    /**
     *
     * @var Axis_Translate
     */
    private static $_instance;

    /**
     * Translators
     *
     * @var array of Zend_Translate
     */
    private static $_translators = null;

    /**
     *
     * @var string
     */
    private $_locale;

    /**
     *
     * @var string
     */
    private static $_module = 'Axis_Core';

    /**
     * Current module
     *
     * @param string $module [optional]
     */
    public function __construct($module = 'Axis_Core', $locale = null)
    {
        if (null === $locale) {
            $this->_locale = Axis_Locale::getLocale()->toString();
        } else {
            $this->_locale = $locale;
        }

        self::$_module = $module;
        
        if ('Axis_Install' !== $module
            && false == Axis::config('core/translation/autodetect')) {

             Zend_Translate::setCache(Axis::cache());
        }
    }

    /**
     * Return instance of Axis_Translate
     *
     * @param  string $module [optional]
     * @static
     * @return Axis_Translate
     */
    public static function getInstance($module = 'Axis_Core', $locale = null)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($module, $locale);
        } elseif (self::$_module !== $module) {

            if (!in_array($module, array_keys(Axis::app()->getModules()))) {
                throw new Axis_Exception(
                    'Translate error : module ' . $module . ' not exist'
                );
            }
            self::$_module = $module;
        }
        return self::$_instance;
    }

    /**
     * Return instance of Zend_Translate_Adapter
     *
     * @param string $module
     * @return Zend_Translate_Adapter|null
     */
    public function getTranslator($module = null)
    {
        if (null === $module) {
            $module = self::$_module;
        }
        if (!isset(self::$_translators[$module])
           ||(!self::$_translators[$module] instanceof Zend_Translate)) {

            $filename = $this->_getFileName($this->_locale, $module);

            if (!is_readable($filename)) {
                return null;
            }

            $translator = new Zend_Translate(
                Zend_Translate::AN_CSV,
                $filename,
                $this->_locale,
                array('delimiter' => ',')
            );
            self::$_translators[$module] = $translator;
        }
        return self::$_translators[$module];
    }

    /**
     *
     * @param string $locale
     * @param string $module
     * @return string
     */
    protected function _getFileName($locale, $module)
    {
        return AXIS_ROOT . '/app/locale/' . $locale . '/' . $module . '.csv';
    }

    /**
     *
     * @param array $args
     * @return string
     */
    public function translate(array $args)
    {
        $text = array_shift($args);

        if ('Axis_Install' !== self::$_module
            && Axis::config('core/translation/autodetect')
            && (null === $this->getTranslator() // translate file not exist
                || !$this->getTranslator()->isTranslated($text, false, $this->_locale))) {

            $this->addTranslate($text, self::$_module);
        }
        if (null === $this->getTranslator()) {
            return @vsprintf($text, $args);
        }

        if (!count($args)) {
            return $this->getTranslator()->_($text, $this->_locale);
        }
        return @vsprintf(
            $this->getTranslator()->_($text, $this->_locale),
            $args
        );
    }

    /**
     * Translate text
     *
     * @param array
     * @return string
     */
    public function __()
    {
        return $this->translate(func_get_args());
    }

    public function getLocale()
    {
        return $this->_locale;
    }

    /*
    protected static function _loadTranslation($module, $locale = '')
    {
        if (empty($locale)) {
            $locale = self::$_instance->_locale;
        }

        $filename = self::_getFileName($locale, $module);
        if (!is_readable($filename)) {
            self::$_instance->getTranslator()
                ->addTranslation($filename, $locale);
        }
        self::$_module = $module;
    }
   // */

    /**
     *
     * @param string $filename
     * @param string $locale
     * @param array $options
     * @return array
     */
    protected function _loadTranslationData(
            $filename, $locale, array $options = array())
    {
        $result = array();
        if (!$file = @fopen($filename, 'rb')) {
            throw new Axis_Exception(
                'Error opening translation file \'' . $filename . '\'.'
            );
        }

        while(($data = fgetcsv(
                $file,
                $options['length'],
                $options['delimiter'],
                $options['enclosure'])
            ) !== false) {

            if (substr($data[0], 0, 1) === '#') {
                continue;
            }
            if (!isset($data[1])) {
                continue;
            }
            if (count($data) == 2) {
                $result[$locale][$data[0]] = $data[1];
            } else {
                $singular = array_shift($data);
                $result[$locale][$singular] = $data;
            }
        }
        fclose($file);
        return isset($result[$locale]) ? $result[$locale] : array();
    }

    /**
     * Add new taransllate (key => value )to localization
     *
     * @param string $text
     * @param string $module
     * @param string $locale
     * @example  addTranslate('name')
     *           -//-('name', '..../app/locale/en_US/Axis_Contacts.php')
     * @return bool
     */

    public function addTranslate($text, $module, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->_locale;
        }

        $filename = $this->_getFileName($locale, $module);

        if (!is_readable($filename)) {

            $dir = dirname($filename);
            if (!is_readable($dir)) {
                mkdir($dir, 0777, true);
            }
            if (!is_writable($dir) && @chmod($dir  , 0777)) {
                Axis::message()->addError(
                    'Can\'t write to folder "' . $dir . '". Permission denied'
                );
                Axis::message()->addNotice(
                   'Workaround: >chmod -R 0777 [root_path]/app/locale'
                );
                return false;
            }
            touch($filename);
            chmod($filename, 0777);
        }

        if (!$file = @fopen($filename, 'a')) {
            throw new Axis_Exception(
                'Error writing translation file \'' . $filename . '\'.'
            );
        }

        fwrite($file, '"' . $text . '","' . $text . "\"\n");
        fclose($file);

        return true;
    }

    /**
     * Returns the set cache
     *
     * @return Zend_Cache_Core The set cache
     */
    public static function getCache()
    {
        return self::$_translators[self::$_module]->getCache();
    }

    /**
     * Sets a cache for all instances of Axis_Translate
     *
     * @param  Zend_Cache_Core $cache Cache to store to
     * @return void
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        if (!self::$_translators[self::$_module] instanceof Zend_Translate) {
            return false;
        }
        return self::$_translators[self::$_module]->setCache($cache);
    }

    /**
     * Returns true when a cache is set
     *
     * @return boolean
     */
    public static function hasCache()
    {
        if (!self::$_translators[self::$_module] instanceof Zend_Translate) {
            return false;
        }
        return self::$_translators[self::$_module]->hasCache();
    }

    /**
     * Removes any set cache
     *
     * @return void
     */
    public static function removeCache()
    {
        if (!self::$_translators[self::$_module] instanceof Zend_Translate) {
            return false;
        }
        return self::$_translators[self::$_module]->removeCache();
    }

    /**
     * Clears all set cache data
     *
     * @return void
     */
    public static function clearCache()
    {
        if (!self::$_translators[self::$_module] instanceof Zend_Translate) {
            return false;
        }
        return self::$_translators[self::$_module]->clearCache();
    }
}