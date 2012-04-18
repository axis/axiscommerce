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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Option_Crypt implements Axis_Config_Option_Encodable_Interface
{
    /**
     *
     * @var Axis_Crypt_Interface 
     */
    protected $_handler = null;
    
    /**
     *
     * @param type $handler
     * @return Axis_Core_Model_Option_Crypt 
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
        if (empty($this->_handler)) {
            $this->_handler = Axis_Crypt::factory();
        }
        return $this->_handler;
    }
        
    /**
     *
     * @param string $value
     * @return string
     */
    public function encode($value)
    {
        return $this->getHandler()->encrypt($value);
    }
    
    /**
     *
     * @param string $value
     * @return string
     */
    public function decode($value)
    {
        return $this->getHandler()->decrypt($value);
    }
}
