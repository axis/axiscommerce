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
 * @package     Axis_Catalog
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Compare
{
    public function __construct()
    {
        if (!isset(Axis::session()->compareList)) {
            Axis::session()->compareList = array();
        }
    }
    
    public function add($productId)
    {
        Axis::session()->compareList[$productId] = $productId;
    }
    
    public function remove($productId)
    {
        if (isset(Axis::session()->compareList[$productId])) {
            unset(Axis::session()->compareList[$productId]);
        }
    }
    
    public function clear()
    {
        Axis::session()->compareList = array();
    }
    
    public function getItems()
    {
        return Axis::session()->compareList;
    }
    
    public function hasItems()
    {
        return count(Axis::session()->compareList) > 0;
    }
}