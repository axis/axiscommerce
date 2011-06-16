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
class Axis_Discount_Model_Eav extends Axis_Db_Table
{
    protected $_name = 'discount_eav';

    protected $_primary = array('discount_id', 'entity', 'value');

    public function insert(array $data)
    {
        if (substr($data['entity'], 0, strlen('date')) === 'date') {
            $data['value'] = strtotime($data['value']);
        }
        return parent::insert($data);
    }

    /**
     *
     * @param int $discountId
     * @return array
     */
    public function getRulesByDiscountId($discountId)
    {
        $rowset = $this->select()
            ->where('discount_id = ?', $discountId)
            ->fetchAll()
            ;
        
        $result = array();
        foreach ($rowset as $row) {
            if (strstr($row['entity'], '_')) {
                list($entity, $etype) = explode('_', $row['entity'], 2);
                $result['conditions'][$entity]['e-type'][] = $etype;
                $value =  $row['value'];
                if (substr($entity, 0, strlen('date')) === 'date') {
                    $value = Axis_Date::timestamp($row['value'])
                        ->toPhpString("Y-m-d");
                }
                $result['conditions'][$entity]['value'][] = $value;
            } else {
                $result[$row['entity']][] = intval($row['value']);
            }
        }
        return $result;
    }

    public function getDiscountIdBySpecialAndProductId($productId)
    {
        return $this->select('discount_id')
            ->join('discount_eav', 'de.discount_id = de2.discount_id')
            ->where("de.entity = 'productId'")
            ->where('de.value = ?', $productId)
            ->where("de2.entity ='special'")
            ->where('de2.value = 1')
            ->fetchOne()
            ;
    }
}