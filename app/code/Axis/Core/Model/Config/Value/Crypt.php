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
 * @subpackage  Axis_Config_Handler
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Config_Value_Crypt implements Axis_Config_Option_Encodable_Interface
{
    /**
     *
     * @var Axis_Crypt_Interface 
     */
    protected $_handler = null;
    
    /**
     *
     * @param Axis_Crypt_Interface $handler 
     */
    public function __construct(Axis_Crypt_Interface $handler = null) 
    {
        if (empty($handler)) {
            $handler = Axis_Crypt::factory();
        }
        $this->setHandler($handler);
    }

    /**
     *
     * @param type $handler
     * @return Axis_Core_Model_Config_Value_Crypt 
     */
    public function setHandler(Axis_Crypt_Interface $handler) 
    {
        $this->_handler = $handler;
        return $this; 
    }
    
    /**
     *
     * @return Axis_Crypt_Interface 
     */
    public function getHandler() 
    {
        return $this->_handler;
    }
        
    /**
     *
     * @param string $value
     * @return string
     */
    public function encodeConfigOptionValue($value)
    {
        return $this->getHandler()->encrypt($value);
    }
    
    /**
     *
     * @param string $value
     * @return string
     */
    public function decodeConfigOptionValue($value)
    {
        return $this->getHandler()->decrypt($value);
    }
}