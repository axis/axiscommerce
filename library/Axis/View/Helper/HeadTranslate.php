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
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_HeadTranslate extends Zend_View_Helper_Placeholder_Container_Standalone
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Axis_View_Helper_HeadTranslate';

    /**
     * Return headTranslate object
     *
     * @return Axis_View_Helper_HeadTranslate
     */
    public function headTranslate()
    {
        return $this;
    }

    /**
     * Add translate file by module name
     *
     * @param string $moduleName
     * @return Axis_View_Helper_HeadTranslate
     */
    public function add($moduleName)
    {
        if (!$this->_isDuplicate($moduleName)) {
            $item = $this->createData($moduleName);
            $this->append($item);
        }
        return $this;
    }

    /**
     * Is the file specified a duplicate?
     *
     * @param  string $moduleName
     * @return bool
     */
    protected function _isDuplicate($moduleName)
    {
        foreach ($this->getContainer() as $item) {
            if ($item->moduleName === $moduleName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Is the script provided valid?
     *
     * @param  mixed $value
     * @param  string $method
     * @return bool
     */
    protected function _isValid($value)
    {
        if ((!$value instanceof stdClass)
            || !isset($value->moduleName)) {

            return false;
        }

        return true;
    }

    /**
     * Override append
     *
     * @param  string $value
     * @return void
     */
    public function append($value)
    {
        if (!$this->_isValid($value)) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('Invalid argument passed to append(); please use one of the helper methods, appendScript() or appendFile()');
        }

        return $this->getContainer()->append($value);
    }

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->_isValid($item)) {
                continue;
            }

            $items[] = $item->moduleName;
        }
        return $indent
            . '<script type="text/javascript" src="'
            . $this->view->resourceUrl
            . '/js/translate.php?f='
            . implode(',', $items)
            . '"></script>'
            . $indent;
    }

    /**
     * Create data item containing all necessary components of script
     *
     * @param  string $moduleName
     * @return stdClass
     */
    public function createData($moduleName)
    {
        $data             = new stdClass();
        $data->moduleName = $moduleName;
        return $data;
    }
}