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
class Axis_Discount_Model_Discount extends Axis_Db_Table
{
    protected $_name = 'discount';
    protected $_primary = 'id';
    protected $_rowClass = 'Axis_Discount_Model_Discount_Row';

    protected function _setListFilter(Zend_Db_Select $select, array $params)
    {
        if (!isset($params['special'])) {
            return $select;
        }
        $subQuery = $this->getAdapter()->select()
            ->from(array('d' => $this->_prefix . 'discount'), array('id'))
            ->join(array('e' => $this->_prefix . 'discount_eav'),
               "e.discount_id = d.id AND e.entity = 'special' AND e.value = '1' ",
               array()
            )
        ;
        $specialDiscountIds = $this->getAdapter()->fetchAll($subQuery);
        if (!count($specialDiscountIds)) {
            return $params['special'] ? $select->where('1 != 1') : $select;
        }

        $condition = $params['special'] ? '' : ' NOT ';
        $select->where($this->getAdapter()->quoteInto(
            "d.id {$condition} IN (?)", $specialDiscountIds
        ));

        return $select;
    }

    public function getList($params)
    {
        $select = $this->getAdapter()->select();
        $select->from(array('d' => $this->_prefix . 'discount'))
            ->order($params['sort'] . ' ' . $params['dir'])
            ->limit($params['limit'], $params['start']);
        $select = $this->_setListFilter($select, $params);
        return $this->getAdapter()->fetchAll($select->__toString());
    }

    public function getCount($params)
    {
        $select = $this->getAdapter()->select()
            ->from(array('d' => $this->_prefix . 'discount'),
                new Zend_Db_Expr('COUNT(*)')
            );

        $select = $this->_setListFilter($select, $params);

        return $this->getAdapter()->fetchOne($select);
    }
    /**
     * @static
     * @return const array
     */
    public static function getPriceConditionTypes()
    {
        return array(
            'equals' => 'Equals',
            'greate' => 'Greater then',
            'less' => 'Less then'
        );
    }

    /**
     *
     * @static
     * @return const array
     */
    public static function getDateConditionTypes()
    {
        return self::getPriceConditionTypes();
    }

    /**
     *  Add/update discount
     *  return discount id
     *
     * @param array $params
     * @return int
     */
    public function save($params)
    {
        if (isset($params['id'])) {
            parent::delete('id = ' . $params['id']);
        }
        if (empty($params['discountFromDate'])) {
            $params['discountFromDate'] = new Zend_Db_Expr('NULL');
        }
        if (empty($params['discountToDate'])) {
            $params['discountToDate'] = new Zend_Db_Expr('NULL');
        }
        if (!isset($params['discountPriority'])
            || empty($params['discountPriority'])) {

            $params['discountPriority'] = 125;
        }
        $discountId = $this->insert(array(
            'name' => $params['discountName'],
            'description' => '', //@todo
            'from_date' => $params['discountFromDate'],
            'to_date' => $params['discountToDate'],
            'is_active' => $params['discountIsActive'],
            'type' => $params['discountOperator'],
            'amount' => $params['discountAmount'],
            'priority' => $params['discountPriority'],
            'is_combined' => $params['discountIsCombined']
        ));
        if (isset($params['discountSites'])) {
            foreach ($params['discountSites'] as $siteId) {
                Axis::single('discount/eav')->insert(array(
                    'discount_id' => $discountId,
                    'entity' => 'site',
                    'value' => $siteId
                ));
            }
        }
        if (isset($params['discountCustomerGroups'])) {
            foreach ($params['discountCustomerGroups'] as $groupId) {
                Axis::single('discount/eav')->insert(array(
                    'discount_id' => $discountId,
                    'entity' => 'group',
                    'value' => $groupId
                ));
            }
        }
        if (isset($params['discountSpecial'])) {
            Axis::single('discount/eav')->insert(array(
                'discount_id' => $discountId,
                'entity' => 'special',
                'value' => $params['discountSpecial']
            ));
        }
        if (isset($params['condition']))
        foreach ($params['condition'] as $conditionType => $subCondition) {

            if (in_array($conditionType, array('category', 'manufacture', 'productId')) ) {
                foreach ($subCondition as $condition) {
                    Axis::single('discount/eav')->insert(array(
                        'discount_id' => $discountId,
                        'entity' => $conditionType,
                        'value' => $condition
                    ));
                }
            } elseif($conditionType == 'attribute') {

                foreach ($subCondition['optionId'] as $id => $optionId) {
                    Axis::single('discount/eav')->insert(array(
                        'discount_id' => $discountId,
                        'entity' => 'optionId',
                        'value' => $optionId
                    ));
                    Axis::single('discount/eav')->insert(array(
                        'discount_id' => $discountId,
                        'entity' => 'option[' . $optionId . ']',
                        'value' => $subCondition['optionValueId'][$id]
                    ));
                }

            } else {
                $countCustomCondition = count($subCondition['e-type']);
                for($i = 0; $i < $countCustomCondition; $i++) {
                    Axis::single('discount/eav')->insert(array(
                        'discount_id' => $discountId,
                        'entity' => $conditionType . '_' . $subCondition['e-type'][$i],
                        'value' => $subCondition['value'][$i]
                    ));
                }
            }
        }
        return $discountId;
    }

    /**
     *  put discount prices to gived productArray
     */
    public function fillDiscount(&$products)
    {
        $discounts = $this->_getDiscountRulesByProductIds(array_keys($products));
        foreach ($discounts as $productId => $discount) {
            if (!isset($discount[0])) {
                continue;
            }
            $products[$productId]['price_discount'] = $this->applyDiscountRule(
                $products[$productId]['price'], $discount[0]
            );
        }
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getRulesByProductId($productId)
    {
        $rules = $this->_getDiscountRulesByProductIds(array(
            $productId
        ));
        return isset($rules[$productId]) ? $rules[$productId] : array();
    }

    /**
     * Filtred
     * @param array $discount
     * @param array $filter
     * @return bool
     */
    private function _setFilter(array $discount, array $filter)
    {
        if (isset($discount['site'])
            && !in_array($filter['siteId'], $discount['site'])) {

            return false;
        }
        if (isset($discount['group'])
            && Axis_Account_Model_Customer_Group::GROUP_ALL_ID !== $filter['customerGroupId']
            && !in_array($filter['customerGroupId'], $discount['group'])) {

            return false;
        }
        if (isset($discount['manufacture'])
            && !in_array($filter['manufacturerId'], $discount['manufacture'])) {

            return false;
        }

        if (isset($discount['category']) &&
            !count(array_intersect($filter['category'], $discount['category']))) {

            return false;
        }
        if (isset($discount['productId'])
            && !in_array($filter['productId'], $discount['productId'])) {

            return false;
        }

        if (isset($discount['date_equals'])) {
            foreach ($discount['date_equals'] as $date) {
                if ($date != strtotime($filter['created_on'])) {
                    return false;
                }
            }
        }
        if (isset($discount['date_greate'])) {
            foreach ($discount['date_greate'] as $date) {
                if ($date > strtotime($filter['created_on'])) {
                    return false;
                }
            }
        }
        if (isset($discount['date_less'])) {
            foreach ($discount['date_less'] as $date) {
                if ($date < strtotime($filter['created_on'])) {
                    return false;
                }
            }
        }
        if (isset($discount['price_equals'])) {
            foreach ($discount['price_equals'] as $price) {
                if ($price != $filter['price']) {
                    return false;
                }
            }
        }
        if (isset($discount['price_greate'])) {
            foreach ($discount['price_greate'] as $price) {
                if ($price > $filter['price']) {
                    return false;
                }
            }
        }
        if (isset($discount['price_less'])) {
            foreach ($discount['price_less'] as $price) {
                if ($price < $filter['price']) {
                    return false;
                }
            }
        }
//        if (isset($filter['special'])) {
//           $discountSpecial = !isset($discount['special']) ? 0 :
//               (isset($discount['special'][0]) ? intval($discount['special'][0]) : 0);
//
//           if ($discountSpecial != intval($filter['special'])) {
//               return false;
//           }
//        }
        return true;
    }

    protected function _AttributeFilter($discount, $filter)
    {
        $result = array();
        if (!isset($discount['optionId'])) {
            return true;
        }
        foreach ($discount['optionId'] as $optionId) {
            if ($result) {
                continue;
            }
            if (!isset($discount['option[' . $optionId . ']'])) {
                continue;
            }
            foreach ($discount['option[' . $optionId . ']'] as $optionValueId) {

                foreach ($filter as $attributeId => $attribute) {
                    if ($attribute['optionId'] == $optionId
                      && $attribute['optionValueId'] == $optionValueId) {

                        $result[$attributeId] = array(
                            'optionId' => $optionId,
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
     * @param int $variationId
     * @return array
     */
    public function getRuleByProductId($productId, $variationId = 0)
    {
        $rules = $this->_getDiscountRulesByProductIds(array($productId));

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
     *
     * @param bool $onlyActive [optional]
     * @param bool $dateFiltred [optional]
     * @return array
     */
    protected function _getAllRules($onlyActive = true, $dateFiltred = true)
    {

        $select = $this->getAdapter()->select();
        $select->from(array('d' => $this->_prefix . 'discount'))
            ->join(array('e' => $this->_prefix . 'discount_eav'),
                "e.discount_id = d.id"
            );
        if ($onlyActive) {
            $select->where('d.is_active = 1');
        }
        if ($dateFiltred) {
            $now = Axis_Date::now()->toPhpString("Y-m-d");
            $select
                ->where('d.from_date <= ? OR d.from_date IS NULL', $now)
                ->where('? <= d.to_date OR d.to_date IS NULL', $now);
        }

        $rowset = $this->getAdapter()->fetchAll($select->__toString());

        $discounts = array();
        foreach ($rowset as $row) {
            if (!isset($discounts[$row['id']])) {
                $discounts[$row['id']] = array(
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'amount' =>  $row['amount'],
                    'priority' => $row['priority'],
                    'is_combined' => $row['is_combined'],
                );
            }
            $discounts[$row['id']][$row['entity']][] = $row['value'];
        }
        return $discounts;
    }

    /**
     * @link http://drupal.org/node/468516#comment-1627620
     * @param array $productIds (productId => price) "if price null than use $row->price"
     * @return array
     */
    protected function _getDiscountRulesByProductIds(
        array $productIds/*, array $externalFilter = array()*/)
    {
        if (!sizeof($productIds)) {
            return array();
        }
        $discounts = $this->_getAllRules();

        $productsRowset = Axis::single('catalog/product')->find($productIds);

        $attributesRowset = Axis::single('catalog/product_attribute')
            ->getAtrributesByProductIds($productIds);

        $categoriesRowset = Axis::single('catalog/product_category')
            ->getCategoriesByProductIds($productIds);

        $variationRowset = Axis::single('catalog/product_variation')
            ->getVariationsByProductIds($productIds);

        //filtred
        $discountRules = array();
        $customerGroupId = Axis::single('account/customer')
            ->getGroupId(Axis::getCustomerId());

        foreach ($productsRowset as $product) {

            $productAttributes = isset($attributesRowset[$product->id][0])
                ? $attributesRowset[$product->id][0] : array();

            $filter = array(
                'siteId'            => Axis::getSiteId(),
                'customerGroupId'   => $customerGroupId,
                'manufacturerId'    => $product->manufacturer_id,
                'category'          => isset($categoriesRowset[$product->id]) ?
                    $categoriesRowset[$product->id] : null,
                'productId'         => $product->id,
                'created_on'        => $product->created_on,
                'price'             => floatval($product->price)//,
                //'attribute' => $productAttributes
            );
//            $filter = array_merge($externalFilter, $filter);

            foreach ($discounts as $discountId => $discount) {

                if (!$this->_setFilter($discount, $filter)) {
                    continue;
                }
                if (!$filtredAttributes = $this->_AttributeFilter(
                        $discount, $productAttributes)) {

                    continue;
                }
                // 0 says that this bas variation
                $discountRules[$product->id][0][$discountId] = array(
                    'name'          => $discount['name'],
                    'type'          => $discount['type'],
                    'amount'        => $discount['amount'],
                    'priority'      => $discount['priority'],
                    'is_combined'   => $discount['is_combined']
                );
                if ($filtredAttributes && is_array($filtredAttributes)) {
                    $discountRules[$product->id][0][$discountId]['attribute']
                        = $filtredAttributes;
                }
            }

            if (!isset($variationRowset[$product->id])
                || !count($variationRowset[$product->id])) {

                continue;
            }
            $variations = $variationRowset[$product->id];

            foreach ($variations as $variation) {
                $filter['price'] = $this->_price(
                    $product->price,
                    $variation['price'],
                    $variation['price_type']
                );

                $baseVariation = isset($attributesRowset[$product->id][0]) ?
                    $attributesRowset[$product->id][0] : array();

                if (isset($attributesRowset[$product->id][$variation['id']])) {
                    $productAttributes =
                        $baseVariation +
                        $attributesRowset[$product->id][$variation['id']]
                    ;
                }
                //$filter['attribute'] = $productAttributes;

                foreach ($discounts as $discountId => $discount) {

                    if (!$this->_setFilter($discount, $filter)) {
                        continue;
                    }
                    if (!$filtredAttributes = $this->_AttributeFilter(
                            $discount, $productAttributes)) {

                        continue;
                    }

                    $discountRules[$product->id][$variation['id']][$discountId] = array(
                        'name'          => $discount['name'],
                        'type'          => $discount['type'],
                        'amount'        => $discount['amount'],
                        'priority'      => $discount['priority'],
                        'is_combined'   => $discount['is_combined']
                    );
                    if ($filtredAttributes && is_array($filtredAttributes)) {

                        $discountRules[$product->id][$variation['id']][$discountId]['attribute']
                            = $filtredAttributes;
                    }
                }
            }

        }

        $discountRules = $this->_sortRulesArrayByPriority($discountRules);

        return $discountRules;
    }

    protected function _sortRulesArrayByPriority($rules)
    {
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

        foreach ($rules as &$ruleset) {
            foreach ($ruleset as &$rule) {
                uasort($rule, '_sortDiscount');
            }
        }

       return $rules;
    }

    private function _price($price, $change, $type)
    {
        $price = floatval($price);
        $change = floatval($change);
        if ($change == 0) {
            return $price;
        }
        switch ($type) {
            case 'to':
                return $change;
            case 'by':
                return $price - $change;
            case 'percent':
                return $price - ($price * $change / 100);
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

        $discountId = $this->insert(array(
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
        Axis::single('discount/eav')->insert(array(
            'discount_id'   => $discountId,
            'entity'        => 'productId',
            'value'         => $productId
        ));
        Axis::single('discount/eav')->insert(array(
            'discount_id'   => $discountId,
            'entity'        => 'special',
            'value'         => true
        ));
        return $discountId;
    }

    /**
     *
     * @param int $productId
     * @return assoc array
     */
    public function getSpecialPrice($productId)
    {
        $rules = $this->_getAllRules(false, false);

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

        $discount = $this->find($discountId)->current();
        return array(
            'price'         => $discount->amount, //always "to"
            'from_date_exp' => $discount->from_date,
            'to_date_exp'   => $discount->to_date
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
        $select = $this->getAdapter()->select();
        $select->distinct()->from(array('d' => $this->_prefix . 'discount'), array())
            ->join(array('e1' => $this->_prefix . 'discount_eav'),
                "e1.discount_id = d.id AND e1.entity = 'special' AND e1.value = '1'",
                array())
            ->join(array('e2' => $this->_prefix . 'discount_eav'),
                "e2.discount_id = e1.discount_id AND e2.entity = 'productId'",
                array('value'))
            ->where('d.is_active = 1')
            ->where('d.from_date <= ? OR d.from_date IS NULL', $date)
            ->where('? <= d.to_date OR d.to_date IS NULL', $date)
            ->order(array('d.from_date DESC', 'cp.id DESC'))
            ->limit($limit)
            ->join(array('cpc' => $this->_prefix . 'catalog_product_category'),
                'e2.value = cpc.product_id',
                array())
            ->join(array('c' => $this->_prefix . 'catalog_category'),
                'cpc.category_id = c.id',
                array())
            ->where('c.site_id = ?', $siteId)
            ->join(array('cp' => $this->_prefix . 'catalog_product'),
                'e2.value = cp.id',
                array())
            ->where('cp.is_active = 1');

        if ($disabledCategories = Axis::single('catalog/category')->getDisabledIds()) {
            $select->where('c.id NOT IN (?)', $disabledCategories);
        }

        if (null != $categoryId) {
            $select->where('cpc.category_id = ? ', $categoryId);
        }
        return $this->getAdapter()->fetchCol($select->__toString());
    }
}