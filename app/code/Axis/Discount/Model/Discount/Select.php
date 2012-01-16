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
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Discount_Model_Discount_Select extends Axis_Db_Table_Select
{
    /**
     *
     * @param bool $status
     * @return Axis_Discount_Model_Discount_Select 
     */
    public function addIsActiveFilter($status = true) 
    {
        return $this->where('d.is_active = ?', (int) $status);
    }

    /**
     *
     * @param Axis_Date $date
     * @return Axis_Discount_Model_Discount_Select 
     */
    public function addDateFilter($date = null) 
    {
        if (null === $date) {
            $date = Axis_Date::now();
        }
        $date = $date->toPhpString("Y-m-d");

        return $this
            ->where('d.from_date <= ? OR d.from_date IS NULL', $date)
            ->where('? <= d.to_date OR d.to_date IS NULL', $date);
    }
    
   /**
    *
    * @param bool $status
    * @return Axis_Discount_Model_Discount_Select 
    */
    public function addSpecialFilter($status = true)
    {
        $select = Axis::model('discount/eav')->select('discount_id')
            ->where('de.entity = ?', 'special')
            ->where('de.value = ?', 1);
        if ($status) {
            return $this->where('d.id = ANY ?', $select);
        }
        return $this->where('d.id <> ALL ?', $select);
    }
    
   /**
    *
    * @param array|int $product
    * @return Axis_Discount_Model_Discount_Select 
    */
    public function addProductFilter($product = array())
    {
        if (!is_array($product)) {
            $product = array($product);
        }
               
        $model = Axis::model('discount/eav');
        
        $select1 = $model->select('discount_id')
            ->distinct()
            ->where('de.entity = ?', 'productId')
            ->where('de.value IN (?)', $product)
            ;
        
        $select = $model->select('discount_id')
            ->distinct()
            ->where('de.entity = ?', 'productId')
            ->where('de.value NOT IN (?)', $product)
            ->where('de.discount_id != ALL ?', $select1)
            ;
        
        $this->where('d.id != ALL ?', $select);
        return $this;
        
    }
}