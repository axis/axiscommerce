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
class Axis_Install_Model_Translate
{
    /**
     * @static
     * @var Axis_Install_Model_Translate
     */
    private static $_instance;

    /**
     *
     * @var Zend_Translate
     */
    private $_zendTranslate;

    /**
     *
     * @var string
     */
    private $_locale = null;

    private function __construct()
    {
        $this->_locale = Axis_Install_Model_Locale::getLocale()->toString();
        $this->_zendTranslate = new Zend_Translate(
            Zend_Translate::AN_CSV,
            $this->_getFileName($this->_locale),
            $this->_locale,
            array('delimiter' => ',')
        );
    }

    /**
     * @static
     * @return Axis_Install_Model_Translate
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     *
     * @param string $locale
     * @param string $module
     * @return string
     */
    protected function _getFileName($locale)
    {
        if (!is_readable(ECART_ROOT . '/app/locale/' . $locale . '/Axis_Install.csv')) {
            $locale = 'en_US';
        }
        return ECART_ROOT . '/app/locale/' . $locale . '/Axis_Install.csv';
    }

    /**
     * Return translator
     *
     * @return Zend_Translate
     */
    public function getTranslator()
    {
        return $this->_zendTranslate;
    }

    /**
     * Translate text
     *
     * @param array
     * @return string
     */
    public function _(array $args)
    {
        $text = array_shift($args);
        return @vsprintf($this->getTranslator()->_($text, $this->_locale), $args);
    }
}

function __()
{
    return Axis_Install_Model_Translate::getInstance()->_(func_get_args());
}