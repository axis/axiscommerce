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

    /**
     * Retrieve the list of product ids
     * which prices are depends on current discount
     *
     * @return array
     */
    public function getApplicableProducts()
    {
        $mDiscountEav = Axis::model('discount/eav');
        $conditions = $mDiscountEav->select('*')
            ->where('discount_id = ?', $this->id)
            ->fetchAll();

        $mProduct = Axis::model('catalog/product');
        $select = $mProduct->select('*')
            ->distinct();

        if (!count($conditions)) {
            return $select->fetchCol();
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
                default:
                    // price and date filters
                    if (0 === strpos($key, 'option')
                        || false === strpos($key, '_')) {

                        continue;
                    }
                    $mapping = array(
                        'equals'    => '=',
                        'greate'    => '>=',
                        'less'      => '<=',
                        'date'      => 'created_on',
                        'price'     => 'price'
                    );
                    list($attribute, $comparator) = explode('_', $key);
                    foreach ($values as $value) {
                        if ('date' === $attribute) {
                            $date = new Axis_Date($value);
                            $value = $date->toString('yyyy-MM-dd', 'iso');
                        }
                        $select->where(
                            "cp.{$mapping[$attribute]} {$mapping[$comparator]} ?",
                            $value
                        );
                    }
                    break;

            }
        }

        return $select->fetchAssoc();
    }
}
