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
 * @subpackage  Axis_Catalog_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Attribute_Row extends Axis_Db_Table_Row
{
    public function isVariation()
    {
        return $this->variation_id != '0';
    }
    
    public function isModifier()
    {
        return $this->modifier == '1' && !$this->isVariation();
    }
    
    public function isProperty()
    {
        return $this->modifier == '0' && !$this->isVariation();
    }
    
    public function isInputable()
    {
        return $this->getOption()->isInputable();
    }
    
    public function getOption()
    {
        return $this->findParentRow('Axis_Catalog_Model_Product_Option', 'Option');
    }
    
    public function getValue()
    {
        if ($this->option_value_id)
            return $this->findParentRow('Axis_Catalog_Model_Product_Option_Value', 'Value');
        return null;
    }
    
    public function getAttributeValues()
    {
        $mAttrValue = Axis::single('catalog/product_attribute_value');
        $select = $mAttrValue->select()
            ->where('product_attribute_id = ?', $this->id);
        return $mAttrValue->fetchAll($select);
    }
    
    public function getVariation()
    {
        return $this->findParentRow('Axis_Catalog_Model_Product_Variation', 'Variation');
    }
}