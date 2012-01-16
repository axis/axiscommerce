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
class Axis_Discount_Model_Discount_Row extends Axis_Db_Table_Row
{
    /**
     * Retrieve the list of product ids
     * which prices are depends on current discount
     *
     * @return array
     */
    public function getApplicableProducts()
    {
        $conditions = Axis::model('discount/eav')->select('*')
            ->where('discount_id = ?', $this->id)
            ->fetchAll();

        $select = Axis::model('catalog/product')->select('*')
            ->distinct();

        if (!count($conditions)) {
            return $select->fetchAssoc();
        }

        $filters = array();
        $joinCategory = false;
        foreach ($conditions as $condition) {
            $filters[$condition['entity']][] = $condition['value'];
            if (in_array($condition['entity'], array('site', 'category'))) {
                $joinCategory = true;
            }
        }

        if ($joinCategory) {
            $select->joinInner(
                'catalog_product_category',
                'cpc.product_id = cp.id'
            );
        }

        foreach ($filters as $key => $values) {
            switch ($key) {
                case 'productId':
                    $select->where('cp.id IN (?)', $values);
                    break;
                case 'manufacture':
                    $select->where('cp.manufacturer_id IN (?)', $values);
                    break;
                case 'site':
                    $select
                        ->joinInner(
                            'catalog_category',
                            'cc.id = cpc.category_id'
                        )
                        ->where('cc.site_id IN (?)', $values);
                    break;
                case 'category':
                    $select->where('cpc.category_id IN (?)', $values);
                    break;
                case 'optionId':
                    $where = array();
                    foreach ($values as $optionId) {
                        $value = $filters['option[' . $optionId . ']'][0];
                        $select->joinInner(
                            array("cpa{$optionId}" => 'catalog_product_attribute'),
                            "cpa{$optionId}.product_id = cp.id"
                        );
                        $where[] = "(cpa{$optionId}.option_id = {$optionId}"
                            . " AND cpa{$optionId}.option_value_id = {$value})";
                    }
                    $select->where(implode(' OR ', $where));
                    break;
                case 'price_greate':
                    $select->where('cp.price >= ?', max($values));
                    break;
                case 'price_less':
                    $select->where('cp.price <= ?', min($values));
                    break;
                default:
                    break;
            }
        }

        return $select->fetchAssoc();
    }
       
    /**
     * 
     * @return Axis_Discount_Model_Rule
     */
    public function getRule()
    {
        $rowset = Axis::model('discount/eav')->select()
            ->where('discount_id = ?', $this->id)
            ->fetchRowset()
            ;
            
        $dataset = array();
        foreach ($rowset as $row) {          
            $dataset[$row->entity][] = $row->value;
        }
        return $dataset;
    }
}
