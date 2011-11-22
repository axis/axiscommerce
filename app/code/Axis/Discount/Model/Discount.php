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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Discount_Model_Discount extends Axis_Db_Table
{
    protected $_name = 'discount';

    protected $_primary = 'id';

    protected $_rowClass = 'Axis_Discount_Model_Discount_Row';

    protected $_selectClass = 'Axis_Discount_Model_Discount_Select';

    /**
     * Filtred
     * @param array $rule
     * @param array $filter
     * @return bool
     */
    private function _setFilter(array $rule, array $filter)
    {
        $filterMapping = array(
            'site'          => 'site',
            'manufacture'   => 'manufacturer',
            'productId'     => 'productId'
        );
        foreach ($filterMapping as $discountKey => $filterKey) {
            if (!isset($rule[$discountKey]) || !isset($filter[$filterKey])) {
                continue;
            }
            if (!in_array($filter[$filterKey], $rule[$discountKey])) {
                return false;
            }
        }
        if (isset($rule['group'])
            && isset($filter['group'])
            && !in_array(Axis_Account_Model_Customer_Group::GROUP_ALL_ID, $rule['group'])
            && !in_array($filter['group'], $rule['group'])) {

            return false;
        }
        if (isset($rule['category'])
            && isset($filter['category'])
            && !count(array_intersect($filter['category'], $rule['category']))) {

            return false;
        }

        $rangeSuffixes = array(
            '_greate'   => '>',
            '_less'     => '<'
        );
        $rangeFiltersMapping = array(
            'price' => 'price'
        );
        foreach ($rangeSuffixes as $suffix => $condition) {
            foreach ($rangeFiltersMapping as $discountKey => $filterKey) {
                if (!isset($rule[$discountKey . $suffix])
                    || !isset($filter[$filterKey])) {

                    continue;
                }
                $filterValue = $filter[$filterKey];
                foreach ($rule[$discountKey . $suffix] as $discountValue) {
                    if ('>' === $condition && $discountValue > $filterValue) {
                        return false;
                    } elseif ('<' === $condition && $discountValue < $filterValue) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function _getAppliedAttributes($rule, $attributes)
    {
        $result = array();
        if (!isset($rule['optionId'])) {
            return true;
        }
        foreach ($rule['optionId'] as $optionId) {
            /*if ($result) {
                continue;
            }*/
            if (!isset($rule['option[' . $optionId . ']'])) {
                continue;
            }
            foreach ($rule['option[' . $optionId . ']'] as $optionValueId) {
                foreach ($attributes as $attributeId => $attribute) {
                    if ($attribute['optionId'] == $optionId
                        && $attribute['optionValueId'] == $optionValueId) {

                        $result[$attributeId] = array(
                            'optionId'      => $optionId,
                            'optionValueId' => $optionValueId
                        );
                    }
                }
            }
        }
               
        return count($result) ? $result : false;
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getRulesByProductId($productId)
    {
        $rules = $this->_getDiscountRulesByProductId($productId);
        
        return isset($rules[$productId]) ? $rules[$productId] : array();
    }
    
    /**
     * @param int $productId
     * @param int $variationId
     * @return array
     */
    public function getRuleByProductId($productId, $variationId = 0)
    {
        $rules = $this->_getDiscountRulesByProductId($productId);

        return isset($rules[$productId][$variationId]) ?
            $rules[$productId][$variationId] : array();
    }

    /**
     *
     * @param float $price
     * @param array $rules
     * @param array $attributeIds
     * @return float
     */
    public function applyDiscountRule(
        $price, array $rules, array $attributeIds = array())
    {
        $first = true;
        foreach ($rules as $rule) {
            if (isset($rule['attribute']) && count($rule['attribute']) &&
                !count(array_intersect(
                        array_keys($rule['attribute']), $attributeIds))) {

                $first = false;
                continue;
            }
            if (!intval($rule['is_combined'])) {
                if ($first) {
                    return $this->_price($price, $rule['amount'], $rule['type']);
                }
                $first = false;
                continue;
            }
            $price = $this->_price($price, $rule['amount'], $rule['type']);
        }
        return $price;
    }

    /**
     * Retreive the list of discounts
     *
     * @param bool $onlyActive [optional]       Filter by is_active column
     * @param bool $dateFiltered [optional]     Filter by current date
     * @return array
     * <pre>
     *  array(
     *      discount_id => array(
     *          id          => int
     *          name        => string
     *          type        => string(to|by|percent)
     *          amount      => float
     *          priority    => int
     *          is_combined => 0
     *          from_date   => date
     *          to_date     => date
     *          [entity]    => int
     *      )
     *      ...
     *  )
     * </pre>
     */
    public function getAllRules($onlyActive = true, $dateFiltered = true)
    {
        $select = $this->select('*')
            ->joinLeft(
                'discount_eav',
                'de.discount_id = d.id',
                '*'
            )
            ->order('d.priority DESC');

        if ($onlyActive) {
            $select->addIsActiveFilter();
        }

        if ($dateFiltered) {
            $select->addDateFilter();
        }

        $discounts = array();
        $rowKeys = array(
            'id',       'name',         'type',         'amount',
            'priority', 'is_combined',  'from_date',    'to_date'
        );
        foreach ($select->fetchAll() as $row) {
            if (!isset($discounts[$row['id']])) {
                foreach ($rowKeys as $key) {
                    $discounts[$row['id']][$key] = $row[$key];
                }
            }
            if (!empty($row['entity'])) {
                $discounts[$row['id']][$row['entity']][] = $row['value'];
            }
        }
        return $discounts;
    }

    /**
     * Returns discounts that can be applied to recieved products
     * Used for price index generation
     *
     * @param array $productIds             Products to search discounts for
     * @param array $discounts [optional]   Search for applicable discounts
     *                                      within supplied array
     * @return array Discounts ordered by priority
     * <pre>
     *  array(
     *      product_id => array(
     *          discount_id => array(
     *              name        => string
     *              type        => string(to|by|percent)
     *              amount      => float
     *              priority    => int
     *              is_combined => 0
     *              from_date   => date
     *              to_date     => date
     *              [entity]    => int
     *          )
     *          ...
     *      )
     *      ...
     *  )
     * </pre>
     */
    public function getApplicableDiscounts(
        array $productIds, array $discounts = array())
    {
        if (!count($discounts)) {
            $discounts = $this->getAllRules();
        }

        $products = Axis::model('catalog/product')->find($productIds);
        $attributesRowset = Axis::model('catalog/product_attribute')
            ->getAtrributesByProductIds($productIds);
        $categoriesRowset = Axis::model('catalog/product_category')
            ->getCategoriesByProductIds($productIds);
        $variationRowset = Axis::model('catalog/product_variation')
            ->getVariationsByProductIds($productIds);

        $result = array();
        foreach ($products as $product) {
            $productAttributes = isset($attributesRowset[$product->id][0])
                ? $attributesRowset[$product->id][0] : array();

            $filter = array(
                'manufacturer'    => $product->manufacturer_id,
                'category'          => isset($categoriesRowset[$product->id]) ?
                    $categoriesRowset[$product->id] : null,
                'productId'         => $product->id,
//                'created_on'        => $product->created_on,
                'price'             => (float) $product->price
            );

            $result[$product['id']] = array();
            foreach ($discounts as $discount) {
                if (!$this->_setFilter($discount, $filter)
                    || !$this->_getAppliedAttributes($discount, $productAttributes)) {

                    continue;
                }
                $result[$product['id']][$discount['id']] = $discount;
            }
        }

        return $result;
    }
    
    /**
     * @link http://drupal.org/node/468516#comment-1627620
     * @param int $productId
     * @return array 
     */
    protected function _getDiscountRulesByProductId($productId) 
    {
        $rules = $this->getAllRules();
        
        $product = Axis::single('catalog/product')->find($productId)->current();
        
        $attributes = array();
        $rowset = Axis::single('catalog/product_attribute')->select()
            ->where('product_id = ?', $productId)
            ->fetchRowset()
            ;
        foreach ($rowset as $row) {
            $attributes[$row->variation_id][$row->id] = array(
                'optionId'       => $row->option_id,
                'optionValueId'  => $row->option_value_id
            );
        }
        
        $categories = Axis::single('catalog/product_category')->select('category_id')
            ->where('product_id = ?', $productId)
            ->fetchCol()
            ;
        
        $variations = Axis::single('catalog/product_variation')->select()
            ->where('product_id = ? OR product_id IS NULL', $productId)
            ->order('id')
            ->fetchAssoc()
            ;
            
        $filter = array(
            'site'         => Axis::getSiteId(),
            'group'        => Axis::single('account/customer')->getGroupId(),
            'manufacturer' => $product->manufacturer_id,
            'category'     => $categories,
            'productId'    => $product->id
        );
        
        $data = array();
        $baseVariationAttributes = isset($attributes[0]) ? $attributes[0] : array();
        foreach ($variations as $variation) {
            
            $filter['price'] = $this->_price(
                $product->price,
                $variation['price'],
                $variation['price_type']
            );
            
            $variationAttributes = isset($attributes[$variation['id']]) ? 
                $attributes[$variation['id']] : array();
            
            $variationAttributes = $baseVariationAttributes + $variationAttributes;
            
            foreach ($rules as $ruleId => $rule) {
                
                if (!$this->_setFilter($rule, $filter)) {
                    continue;
                }
                
                if (!$appliedAttributes = $this->_getAppliedAttributes(
                        $rule, $variationAttributes)) {

                    continue;
                }
                
                $rawRule = array(
                    'name'        => $rule['name'],
                    'type'        => $rule['type'],
                    'amount'      => $rule['amount'],
                    'priority'    => $rule['priority'],
                    'is_combined' => $rule['is_combined']
                );
                
                if ($appliedAttributes && is_array($appliedAttributes)) {

                    $rawRule['attribute'] = $appliedAttributes;
                }
                
                $data[$product->id][$variation['id']][$ruleId] = $rawRule;
            }
        }
        
        // Sort discount rules by priority
        if (!function_exists('_sortDiscount')) {
            function _sortDiscount($a, $b)
            {
                if ($a['priority'] == $b['priority']) {
                    return 0;
                }
                return $a['priority'] > $b['priority'] ? -1 : 1;
            }
        }

        foreach ($data as &$ruleset) {
            foreach ($ruleset as &$rule) {
                uasort($rule, '_sortDiscount');
            }
        }
        
        return $data;
    }

    private function _price($price, $amount, $type)
    {
        $price = floatval($price);
        $amount = floatval($amount);
        if ($amount == 0) {
            return $price;
        }
        switch ($type) {
            case 'to':
                return $amount;
            case 'by':
                return $price - $amount;
            case 'percent':
                return $price - ($price * $amount / 100);
            default:
                return $price;
        }

    }

    /**
     *
     * @param int $productId
     * @param int $price
     * @param string $fromDate
     * @param string $toDate
     * @return int
     */
    public function setSpecialPrice(
        $productId, $price, $fromDate = null, $toDate = null)
    {
        if (empty($fromDate)) {
            $fromDate = null;
        }
        if (empty($toDate)) {
            $toDate = null;
        }
        $productName = Axis::single('catalog/product_description')
            ->find($productId, Axis_Locale::getLanguageId())->current()->name;
        $discountName = 'Special price ' . $productName;

        $row = $this->createRow(array(
            'name'          => $discountName,
            'description'   => '',
            'from_date'     => $fromDate,
            'to_date'       => $toDate,
            'is_active'     => true,
            'type'          => 'to',
            'amount'        => $price,
            'priority'      => 255, //tinyint unsigned max is 255
            'is_combined'   => 0
        ));
        $row->save();
        Axis::single('discount/eav')->createRow(array(
            'discount_id'   => $row->id,
            'entity'        => 'productId',
            'value'         => $productId
        ))->save();
        Axis::single('discount/eav')->createRow(array(
            'discount_id'   => $row->id,
            'entity'        => 'special',
            'value'         => true
        ))->save();
        return $row;
    }

    /**
     *
     * @param int $productId
     * @return assoc array
     */
    public function getSpecialPrice($productId)
    {
        $rules = $this->getAllRules(false, false);

        $discountId = false;
        foreach ($rules as $ruleId => $rule) {

            if (!isset($rule['special'])
                || intval(current($rule['special'])) != 1) {

                continue;
            }
            if (!isset($rule['productId'])
                || !in_array($productId, $rule['productId'])) {

                continue;
            }
            $discountId = $ruleId;
        }
        if (!$discountId) {
            return array();
        }

        $row = $this->find($discountId)->current();
        return array(
            'price'         => $row->amount, //always "to"
            'from_date_exp' => $row->from_date,
            'to_date_exp'   => $row->to_date
        );
    }

    /**
     *
     * @param int $categoryId
     * @param int $limit
     * @param string $date
     * @param int $siteId
     * @return array of int
     */
    public function getSpecialProducts(
        $categoryId = null, $limit = 1, $date = null, $siteId = null)
    {
        if (null === $siteId) {
            $siteId = Axis::getSiteId();
        }
        if (null === $date) {
            $date = Axis_Date::now()->toPhpString("Y-m-d");
        }
        $select = $this->select('de2.value')
            ->distinct()
            ->join('discount_eav',
                "de.discount_id = d.id AND de.entity = 'special' AND de.value = '1'"
            )
            ->join('discount_eav',
                "de2.discount_id = d.id AND de2.entity = 'productId'"
            )
            ->where('d.is_active = 1')
            ->where('d.from_date <= ? OR d.from_date IS NULL', $date)
            ->where('? <= d.to_date OR d.to_date IS NULL', $date)
            ->order(array('d.from_date DESC', 'cp.id DESC'))
            ->limit($limit)
            ->join('catalog_product_category', 'de2.value = cpc.product_id')
            ->join('catalog_category', 'cpc.category_id = cc.id')
            ->where('cc.site_id = ?', $siteId)
            ->join('catalog_product', 'de2.value = cp.id')
            ;

        if ($disabledCategories = Axis::single('catalog/category')->getDisabledIds()) {
            $select->where('cc.id NOT IN (?)', $disabledCategories);
        }

        if (null != $categoryId) {
            $select->where('cpc.category_id = ? ', $categoryId);
        }
        return $select->fetchCol();
    }
}