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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Discount_Model_Discount_Row extends Axis_Db_Table_Row
{
    /**
     * Get info about discount + discount rules (condition)
     *
     * @return array
     */
    public function getCustomInfo()
    {
        $array = $this->toArray();
        $rules = Axis::single('discount/eav')->getRulesByDiscountId($this->id);

        if (isset($rules['conditions'])) {
            $array['conditions'] = $rules['conditions'];
        }
        if (isset($rules['category'])) {
            $array['category'] = $rules['category'];
        }
        if (isset($rules['productId'])) {
            $array['productId'] = $rules['productId'];
        }

        if (isset($rules['manufacture'])) {
            $array['manufacture'] = array_intersect(
                $rules['manufacture'],
                array_keys(Axis_Collect_Manufacturer::collect())
            );
        }
        if (isset($rules['site'])) {
            $array['site_ids'] = array_intersect(
                $rules['site'], 
                array_keys(Axis_Collect_Site::collect())
            );
        }
        if (isset($rules['group'])) {
            $array['customer_group_ids'] = array_intersect(
                $rules['group'], 
                array_keys(Axis_Collect_CustomerGroup::collect())
            );
        }
        
        if (isset($rules['special'])) {
            $array['special'] = current($rules['special']);
        }
        if (isset($rules['optionId'])) {
            foreach ($rules['optionId'] as $optionId) {
                foreach ($rules['option[' . $optionId . ']'] as $optionValueId) {
                    $array['attributes'][] = array(
                        'optionId' => $optionId,
                        'optionValueId' => $optionValueId
                    );
                }
            }
            
        }

        return $array;
    }
    
}