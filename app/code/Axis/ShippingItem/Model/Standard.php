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
 * @package     Axis_ShippingItem
 * @subpackage  Axis_ShippingItem_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_ShippingItem
 * @subpackage  Axis_ShippingItem_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingItem_Model_Standard extends Axis_Method_Shipping_Model_Abstract
{
    protected $_code = 'Item_Standard';
    protected $_title = 'Item';
    protected $_description = 'Shipping, Item, description';
    
    public function __construct($type = null)
    {
        parent::__construct($type);
        $this->_icon = $this->_config->icon;
    }
    
    public function getAllowedTypes($request)
    {
        $method = array(
            'id' => $this->_code,
            'title' => $this->getTitle(),
            'price' => (is_numeric($this->_config->price) ? 
                floatval($this->_config->price) : 0) * $request['qty']
        );
        
        if ($this->_icon) {
            $method['icon'] = $this->getIcon();
        }
        $this->_types[] = $method;
        return $this->_types;
    }
}