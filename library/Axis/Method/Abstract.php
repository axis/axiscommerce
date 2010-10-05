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
 * @package     Axis_Checkout
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Method
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Method_Abstract
{
    /**
     *
     * @var string
     */
    protected $_code;
    protected $_section;
    protected $_logger;
    protected $_title;
    protected $_description;
    protected $_config;
    protected $_file;
    protected $_icon;
    
    public function __construct()
    {
        $this->_config = Axis::config()->{$this->_section}->{$this->getCode()};
        $this->init();
    }
    
    public function init(){}
       
    public function isEnabled()
    {
        return $this->_config->enabled;
    }
    
    public function getCode()
    {
        return $this->_code;
    }
        
    /**
     * Return module name by method code 
     * @return string 
     */
    public function getModuleName()
    {
        list($moduleName, $fileName) = explode('_Model_', get_class($this), 2);
        return $moduleName;
    }
    
    /**
     * Return method file name by method code 
     * @return string 
     */
    public function getFileName()
    {
        list($moduleName, $fileName) = explode('_Model_', get_class($this), 2);
        return $fileName;
    }
    
    public function getTitle()
    {
        return $this->_title;
    }
    
    public function getDescription()
    {
        return $this->_description;
    }
    
    public function getIcon()
    {
        return $this->_icon;
    }
    
    public function config($key = null)
    {
        if (null === $key) {
            return $this->_config;
        }
        return null === $this->_config ? null : $this->_config->$key;
    }
}