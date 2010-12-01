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
 * @package     Axis_Config
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @uses        Zend_Config
 * @category    Axis
 * @package     Axis_Config
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Config extends Zend_Config
{
    const MULTI_SEPARATOR = ',';

    /**
     * @param  array   $array
     * @param  boolean $allowModifications
     * @return void
     */
    public function __construct(array $array, $allowModifications = false)
    {
        $this->_allowModifications = (boolean) $allowModifications;
        $this->_loadedSection = null;
        $this->_index = 0;
        $this->_data = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->_data[$key] = new self($value, $this->_allowModifications);
            } else {
                $this->_data[$key] = $value;
            }
        }
        $this->_count = count($this->_data);
    }

    public function get($name, $siteId = null, $default = null)
    {
        if (strstr($name, '/')) {
            $sections = explode('/', $name);
        } else {
            $sections = array($name);
        }

        $section = array_shift($sections);
        if (!array_key_exists($section, $this->_data)) {
            $this->_load($section, $siteId, $default);
        }

        $result = isset($this->_data[$section]) ? $this->_data[$section] : $default;

        foreach ($sections as $section) {
            if (!$result instanceof Axis_Config) {
                $result = $default;
                break;
            }
            $result = isset($result->_data[$section]) ? $result->_data[$section] : $default;
        }

        return $result;
    }

    private function _load($key, $siteId, $default)
    {
        if (null === $siteId) {
            $siteId = Axis::getSiteId();
        }

        $rows = Axis::single('core/config_field')->getFieldsByKey($key, $siteId);

        if (!sizeof($rows)) {
            $this->_data[$key] = $default;
            return;
        }

        $values = array();
        foreach ($rows as $path => $row) {
            $parts = explode('/', $path);
            switch ($row['config_type']) {
                case 'string':
                    $value = $row['value'];
                    break;
                case 'select':
                    $value = $row['value'];
                    break;
                case 'bool':
                    $value = (bool) $row['value'];
                    break;
                case 'handler':
                    $class = 'Axis_Config_Handler_' . ucfirst($row['model']);
                    if ($row['model']) {
                        $value = call_user_func(array($class, 'getConfig'), $row['value']);
                    } else {
                        $value = $row['value'];
                    }
                    break;
                case 'multiple':
                    if (empty ($row['value'])) {
                        $value = array();
                    } else {
                        $value = explode(self::MULTI_SEPARATOR, $row['value']);
                    }
                    break;
                default:
                    $value = $row['value'];
                    break;
            }
            $values[$parts[0]][$parts[1]][$parts[2]] = $value;
        }
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $this->_data[$key] = new Axis_Config($value, $this->_allowModifications);
            } else {
                $this->_data[$key] = $value;
            }
        }
    }

}