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
class Axis_Catalog_Model_Product_Price_Index extends Axis_Db_Table
{
    protected $_name = 'catalog_product_price_index';

    protected $_primary = 'id';

    protected $_productData = array();

    /**
     * Update price indexes for recieved products
     *
     * @param array $products Keys are the product id
     * @return void
     */
    public function updateIndexesByProducts(array $products)
    {
        if (!count($products)) {
            return;
        }
        $ids = array_keys($products);

        $oldPricesTemp = $this->select('*')
            ->where('product_id IN (?)', $ids)
            ->fetchAll();
        $oldPrices = array();
        foreach ($oldPricesTemp as $oldPrice) {
            $oldPrices[$oldPrice['product_id']][] = $oldPrice;
        }

        $variationsTemp = Axis::model('catalog/product_variation')->select()
            ->where('product_id IN (?)', $ids)
            ->fetchAll();
        $variations = array();
        foreach ($variationsTemp as $variation) {
            $variations[$variation['product_id']][] = $variation;
        }
        foreach ($products as $product) {
            $variations[$product['id']][] = array(
                'id'            => 0,
                'product_id'    => $product['id'],
                'quantity'      => 0,
                'price'         => 0,
                'price_type'    => 'by',
                'weight'        => 0,
                'weight_type'   => 'by'
            );
        }

        $modifiersTemp = Axis::model('catalog/product_attribute')->select('*')
            ->joinInner(
                'catalog_product_option',
                'cpo.id = cpa.option_id',
                'input_type'
            )
            ->where('cpa.product_id IN (?)', $ids)
            ->where('cpa.modifier = 1')
            ->where('cpa.variation_id = 0')
            ->fetchAll();
        $modifiers = array();
        foreach ($modifiersTemp as $modifier) {
            $modifiers[$modifier['product_id']][] = $modifier;
        }

        $productToSites = Axis::model('catalog/product_category')
            ->getSitesByProductIds($ids);

        $mDiscount = Axis::model('discount/discount');
        $discounts = $mDiscount->getApplicableDiscounts($ids);

        $customerGroups = array_filter( // remove 'All groups' group
            array_keys(Axis_Account_Model_Customer_Group::collect())
        );

        foreach ($products as $product) {
            if (!isset($productToSites[$product['id']])) {
                $this->delete('product_id = ' . $product['id']);
                continue;
            }

            $newPrices = $this
                ->setProductData(array(
                    'id'                    => $product['id'],
                    'price'                 => $product['price'],
                    'site_ids'              => $productToSites[$product['id']],
                    'customer_group_ids'    => $customerGroups,
                    'modifiers'             => isset($modifiers[$product['id']]) ?
                        $modifiers[$product['id']] : array(),
                    'variations'            => $variations[$product['id']],
                    'discounts'             => isset($discounts[$product['id']]) ?
                        $discounts[$product['id']] : array()
                ))
                ->getPriceIndexes();

            $oldAssocPrices = array();
            if (isset($oldPrices[$product['id']])
                && count($oldPrices[$product['id']])) {

                foreach ($oldPrices[$product['id']] as $oldPrice) {
                    $oldAssocPrices[$oldPrice['id']] = $oldPrice;
                }
                $this->delete(
                    Axis::db()->quoteInto('id IN (?)', array_keys($oldAssocPrices))
                );
            }

            foreach ($newPrices as $price) {
                $this->insert($price);
            }

            Axis::dispatch('catalog_product_price_update_after', array(
                'product_data' => $product,
                'new_price' => $newPrices,
                'old_price' => $oldAssocPrices
            ));
        }
    }

    /**
     * Update price indexes for recieved customer groups
     *
     * @param array $ids Customer group ids
     * @return void
     */
    public function updateIndexesByCustomerGroupIds(array $ids)
    {
        $products = Axis::model('catalog/product')
            ->select('*')
            ->fetchAssoc();

        if (!count($products)) {
            return;
        }
        $productIds = array_keys($products);

        $variationsTemp = Axis::model('catalog/product_variation')->select()
            ->fetchAll();
        $variations = array();
        foreach ($variationsTemp as $variation) {
            $variations[$variation['product_id']][] = $variation;
        }
        foreach ($products as $product) {
            $variations[$product['id']][] = array(
                'id'            => 0,
                'product_id'    => $product['id'],
                'quantity'      => 0,
                'price'         => 0,
                'price_type'    => 'by',
                'weight'        => 0,
                'weight_type'   => 'by'
            );
        }

        $modifiersTemp = Axis::model('catalog/product_attribute')->select('*')
            ->joinInner(
                'catalog_product_option',
                'cpo.id = cpa.option_id',
                'input_type'
            )
            ->where('cpa.modifier = 1')
            ->where('cpa.variation_id = 0')
            ->fetchAll();
        $modifiers = array();
        foreach ($modifiersTemp as $modifier) {
            $modifiers[$modifier['product_id']][] = $modifier;
        }

        $productToSites = Axis::model('catalog/product_category')
            ->getSitesByProductIds($productIds);

        $mDiscount = Axis::model('discount/discount');
        $discounts = $mDiscount->getApplicableDiscounts($productIds);

        foreach ($products as $product) {
            if (!isset($productToSites[$product['id']])) {
                $this->delete('product_id = ' . $product['id']);
                continue;
            }

            $newPrices = $this
                ->setProductData(array(
                    'id'                    => $product['id'],
                    'price'                 => $product['price'],
                    'site_ids'              => $productToSites[$product['id']],
                    'customer_group_ids'    => $ids,
                    'modifiers'             => isset($modifiers[$product['id']]) ?
                        $modifiers[$product['id']] : array(),
                    'variations'            => $variations[$product['id']],
                    'discounts'             => isset($discounts[$product['id']]) ?
                        $discounts[$product['id']] : array()
                ))
                ->getPriceIndexes();

            foreach ($newPrices as $price) {
                $this->insert($price);
            }
        }
    }

    /**
     * Set the data for the indexes generation
     *
     * @param array $data
     * - id                 = product id
     * - price              = product price
     * - site_ids           = array of site ids where the product lies in
     * - customer_group_ids = array customer group ids
     * - variations         = product variations array
     * - modifiers          = product modifiers array
     * - discounts          = discounts that is applicable with product
     * @return Axis_Catalog_Model_Product_Price_Index
     */
    public function setProductData(array $data)
    {
        $this->_productData = $data;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getProductData($key = null)
    {
        if (null === $key) {
            return $this->_productData;
        }
        return $this->_productData[$key];
    }

    /**
     * Retrieve rowset of price indexes
     * Use this method after calling the setProductData method
     *
     * @return @array
     */
    public function getPriceIndexes()
    {
        list($prices, $rules, $optionsInfo) = $this->_getBasePrices();
        $prices = $this->_addModifierAmounts($prices, $optionsInfo);

        $minPrice = min($prices[0]['min']);
        $maxPrice = max($prices[0]['max']);
        $rowset = array();
        foreach ($rules as $discountIds => $_rules) {
            $rowData = array(
                'product_id'        => $this->getProductData('id'),
                'min_price'         => $minPrice,
                'max_price'         => $maxPrice,
                'final_min_price'   => min($prices[$discountIds]['min']),
                'final_max_price'   => max($prices[$discountIds]['max'])
            );
            foreach ($_rules as $rule) {
                $rowset[] = array_merge($rowData, $rule);
            }
        }
        return $rowset;
    }

    /**
     * Collects the possible base prices and prices with discount applied
     * of the product from variations and modifiers
     *
     * @return array Prices, discountRules and optionsInfo arrays
     * <pre>
     *  prices:
     *      0:
     *          max
     *              id => price
     *          min
     *              id => price
     *      discount_id1:
     *          max:
     *              id => price
     *          min:
     *              id => price
     *      discount_id1_discount_id2 => array() // combined discounts
     *
     *  discountRules:
     *      discount_id1:
     *          rulesArray:
     *              site_id             => int [optional]
     *              customer_group_id   => int [optional]
     *              time_from           => int [optional]
     *              time_to             => int [optional]
     *          rulesArray:
     *              ...
     *      discount_id1_discount_id2 => array() // combined discounts
     *
     *  optionsInfo:
     *      option_id:
     *          type        => string|radio|select|checkbox|textarea|file
     *          required    => boolean
     *      ...
     * </pre>
     */
    protected function _getBasePrices()
    {
        $price = (float) $this->getProductData('price');
        $basePrices = array();
        $basePrices['variation'][0] = $price;
        foreach ($this->getProductData('variations') as $variation) {
            switch ($variation['price_type']) {
                case 'to':
                    $basePrices['variation'][$variation['id']] =
                        (float) $variation['price'];
                    break;
                case 'by':
                    $basePrices['variation'][$variation['id']] =
                        $variation['price'] + $price;
                    break;
                case 'percent':
                    $basePrices['variation'][$variation['id']] =
                        ($variation['price'] / 100 + 1) * $price;
                    break;
            }
        }

        // modifier can affect the base price too, when price_type is 'to'
        $optionsInfo = array();
        foreach ($this->getProductData('modifiers') as $modifier) {
            if (!isset($optionsInfo[$modifier['option_id']])) {
                $optionsInfo[$modifier['option_id']] = array(
                    'type'      => $modifier['input_type'],
                    'required'  => in_array($modifier['input_type'], array(
                        Axis_Catalog_Model_Product_Option::TYPE_RADIO,
                        Axis_Catalog_Model_Product_Option::TYPE_SELECT
                    ))
                );
            }
            if ('to' === $modifier['price_type']) {
                $basePrices['modifier'][$modifier['option_id']][] =
                    (float) $modifier['price'];
            }
        }

        // get extremums from collected base prices
        $prices = array();
        $maxVariationPrice = max($basePrices['variation']);
        $minVariationPrice = min($basePrices['variation']);
        foreach ($basePrices['variation'] as $_vId => $_price) {
            if ($maxVariationPrice == $_price) {
                $prices['max']['v_' . $_vId] = $_price;
            }
            if ($minVariationPrice == $_price) {
                $prices['min']['v_' . $_vId] = $_price;
            }
        }
        if (isset($basePrices['modifier'])) {
            foreach ($basePrices['modifier'] as $_oId => $priceArr) {
                $prices['max']['m_' . $_oId] = max($priceArr);
                $prices['min']['m_' . $_oId] = min($priceArr);
            }
        }
        // end of base price calculations

        $finalPrices = array(
            'prices'    => array(),
            'rules'     => array()
        );
        foreach ($this->getProductData('discounts') as $discount) {
            $finalPrices = $this->_combineDiscount(
                $discount,
                $finalPrices,
                $prices
            );
            if (isset($discount['special'])
                && $discount['special']
                && !isset($discount['from_date'])
                && !isset($discount['to_date'])) {

                // global special price without date limitations
                break;
            }
        }

        $finalPrices['rules'][0] = $this->_getDiscountRules(array());
        foreach ($finalPrices['rules'] as $discountIds => $_rules) {
            $finalPrices['rules'][$discountIds] = $this->_convertRulesToFlat($_rules);
        }
        $finalPrices['rules'] = $this->_cleanupDuplicateRules($finalPrices['rules']);

        $prices = array($prices) + $finalPrices['prices'];

        return array($prices, $finalPrices['rules'], $optionsInfo);
    }

    /**
     * Add modifier amounts to all prices
     *
     * @param array $prices
     * @param array $optionsInfo
     * @return array
     */
    protected function _addModifierAmounts($prices, $optionsInfo)
    {
        $typeCheckbox = Axis_Catalog_Model_Product_Option::TYPE_CHECKBOX;
        foreach ($prices as $_discountId => $_prices) {
            foreach ($_prices as $_extremum => $_basePrices) {
                foreach ($_basePrices as $_vId => $_price) {
                    $previousAmount = array();
                    $priceCalculated = $_price;
                    foreach ($this->getProductData('modifiers') as $modifier) {
                        if ('to' === $modifier['price_type']) {
                            continue;
                        }

                        list($_basedOn, $_cleanId) = explode('_', $_vId);

                        if ('m' === $_basedOn
                            && $typeCheckbox != $optionsInfo[$_cleanId]['type']
                            && $modifier['option_id'] == $_cleanId) {
                            // we can't calculate amount of the modifier
                            // if it was used for current base price
                            // and it's not a checkbox
                            continue;
                        }

                        if ('percent' === $modifier['price_type']) {
                            $amount = $modifier['price'] * $_price / 100;
                        } else {
                            $amount = (float) $modifier['price'];
                        }

                        if ($typeCheckbox == $modifier['input_type']) {
                            if ('max' === $_extremum) {
                                $hasExtremum = ($amount > 0);
                            } else {
                                $hasExtremum = ($amount < 0);
                            }

                            if ($hasExtremum) {
                                $priceCalculated += $amount;
                            }
                        } else {
                            if (!isset($previousAmount[$modifier['option_id']])) {
                                $previousAmount[$modifier['option_id']] = array(
                                    'max' => null,
                                    'min' => null
                                );
                            }

                            // single select modifier
                            if ('max' === $_extremum) {
                                $hasExtremum = (null === $previousAmount[$modifier['option_id']]['max']
                                    || $amount > $previousAmount[$modifier['option_id']]['max']);
                            } else {
                                $hasExtremum = (null === $previousAmount[$modifier['option_id']]['min']
                                    || $amount < $previousAmount[$modifier['option_id']]['min']);
                            }

                            if ($hasExtremum) {
                                $priceCalculated = $priceCalculated
                                    - $previousAmount[$modifier['option_id']][$_extremum]
                                    + $amount;
                                $previousAmount[$modifier['option_id']]
                                    [$_extremum] = $amount;
                            }
                        }
                    }
                    $prices[$_discountId][$_extremum][$_vId] = $priceCalculated;
                }
            }
        }
        return $prices;
    }

    /**
     * Detectects is rules can be merged into third rule
     * with more discounts applied
     *
     * @param array $rule1
     * @param array $rule2
     * @return boolean
     */
    protected function _canCombineRules(array $rule1, array $rule2)
    {
        foreach ($rule1 as $key => $values) {
            if (in_array($key, array('time_from', 'time_to'))) {
                if ('time_from' === $key) {
                    if ($rule2['time_to'][0] < $values[0]) {
                        return false;
                    }
                } elseif ($rule2['time_from'][0] > $values[0]) { // time_to
                    return false;
                }
            } elseif (!count(array_intersect($rule2[$key], $values))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Combines two discount rules into one
     * Example:
     * rule1: site_id: 1,2,5; customer_group_id: 3
     * rule2: site_id: 3,5
     * Result: site_id: 5, customer_group_id: 3
     *
     * @param array $rule1
     * @param array $rule2
     * @return array
     */
    protected function _combineRules(array $rule1, array $rule2)
    {
        $result = array();
        foreach (array('time_from', 'time_to') as $dateRule) {
            if ('time_from' === $dateRule) {
                $result[$dateRule] = array(
                    max($rule1[$dateRule][0], $rule2[$dateRule][0])
                );
            } else {
                $result[$dateRule] = array(
                    min($rule1[$dateRule][0], $rule2[$dateRule][0])
                );
            }
            unset($rule1[$dateRule]);
            unset($rule2[$dateRule]);
        }

        foreach ($rule1 as $key => $values) {
            $result[$key] = array_intersect($rule2[$key], $values);
        }
        return $result;
    }

    /**
     * Prepare discount rules to database insertion
     * Transforms the complex rule into several simple rules:
     * <pre>
     * rules:
     *  site_id: 1,4; customer_group_id: 2,3
     * result:
     *  site_id: 1; customer_group_id:2
     *  site_id: 1; customer_group_id:3
     *  site_id: 4; customer_group_id:2
     *  site_id: 4; customer_group_id:3
     * </pre>
     *
     * @param array $rules
     * @return array
     */
    protected function _convertRulesToFlat(array $rules)
    {
        $result = array(array());
        foreach ($rules as $key => $values) {
            $temp = array();
            foreach ($values as $value) {
                foreach ($result as $res) {
                    $temp[] = array_merge($res, array($key => $value));
                }
            }
            $result = $temp;
        }
        return $result;
    }

    /**
     * Retrieve the dynamic parameters from discount.
     * Customer group, Site, Date from and Date to
     * If false is returned - discount can't be applied
     *
     * @param array $discount
     * @return array
     */
    protected function _getDiscountRules(array $discount)
    {
        $rules = array();
        $rulesMapping = array(
            'group'     => 'customer_group_id',
            'site'      => 'site_id',
            'from_date' => 'time_from',
            'to_date'   => 'time_to'
        );
        foreach ($rulesMapping as $discountKey => $ruleKey) {
            if (!isset($discount[$discountKey])) {
                switch ($ruleKey) {
                    case 'customer_group_id':
                        $rules[$ruleKey] = $this->getProductData('customer_group_ids');
                        break;
                    case 'site_id':
                        $rules[$ruleKey] = $this->getProductData('site_ids');
                        break;
                    case 'time_from':
                        $rules[$ruleKey] = array(
                            Axis_Date::now()->getDate()->getTimestamp()
                        );
                        break;
                    case 'time_to':
                        $rules[$ruleKey] = array(
                            Axis_Date::now()->getDate()->addYear(5)->getTimestamp()
                        );
                        break;
                }
                continue;
            }
            if (!is_array($discount[$discountKey])) {
                // time_from and time_to filters
                $date = new Axis_Date($discount[$discountKey], 'yyyy-MM-dd');
                if ('time_to' === $ruleKey) {
                    $date->set('23:59:59', Zend_Date::TIMES);
                }
                $discount[$discountKey] = array($date->getTimestamp());
            }

            if ('customer_group_id' === $ruleKey) {
                if (in_array(0, $discount[$discountKey])) { // All groups value support
                    $customerGroupIds = $this->getProductData('customer_group_ids');
                } else {
                    $customerGroupIds = array_intersect( // Generate prices only for supplied groups
                        $discount[$discountKey],
                        $this->getProductData('customer_group_ids')
                    );
                }
                $rules[$ruleKey] = $customerGroupIds;
            } else {
                $rules[$ruleKey] = $discount[$discountKey];
            }
        }
        return $rules;
    }

    /**
     * Adds discount price to the final prices array.
     * Apply discount to other dicount prices if possible to.
     *
     * @param array $discount
     * @param array $finalPrices
     * @param array $discounts
     * @param array $basePrices
     * @return array
     */
    protected function _combineDiscount(
        array $discount, array $finalPrices, array $basePrices)
    {
        $discountRules = $this->_getDiscountRules($discount);

        foreach ($basePrices as $_extremum => $_basePrices) {
            foreach ($_basePrices as $_vId => $_price) {
                $discountPrice = $_price;
                if ('by' === $discount['type']) {
                    $discountPrice -= $discount['amount'];
                } elseif ('percent' === $discount['type']) {
                    $discountPrice -= $discount['amount'] * $_price / 100;
                } elseif ('to' === $discount['type']) {
                    $discountPrice = $discount['amount'];
                }
                $finalPrices['prices'][$discount['id']]
                    [$_extremum][$_vId] = $discountPrice;
            }
        }
        $finalPrices['rules'][$discount['id']] = $discountRules;

        if (!$discount['is_combined']) {
            return $finalPrices;
        }

        $discounts = $this->getProductData('discounts');
        foreach ($finalPrices['prices'] as $_discountIds => $_prices) {
            $discountIds = explode('_', $_discountIds);
            if (in_array($discount['id'], $discountIds)) {
                continue;
            }
            if (!$discounts[$discountIds[0]]['is_combined']) {
                continue;
            }
            if (!$this->_canCombineRules(
                    $discountRules, $finalPrices['rules'][$_discountIds])) {

                continue;
            }

            $finalPrices['rules'][$_discountIds . '_' . $discount['id']]
                = $this->_combineRules(
                    $discountRules,
                    $finalPrices['rules'][$_discountIds]
                );
            foreach ($_prices as $_extremum => $_basePrices) {
                foreach ($_basePrices as $_vId => $_price) {
                    $discountPrice = $discount['amount'];
                    if ('by' === $discount['type']) {
                        $discountPrice = $_price - $discount['amount'];
                    } elseif ('percent' === $discount['type']) {
                        $amount = $discount['amount']
                            * $basePrices[$_extremum][$_vId] / 100;
                        $discountPrice = $_price - $amount;
                    }

                    $finalPrices['prices'][$_discountIds . '_' . $discount['id']]
                        [$_extremum][$_vId] = $discountPrice;
                }
            }
        }
        return $finalPrices;
    }

    /**
     * Removes equal price rules and fixes the
     * date intersections between overlapped discounts
     *
     * <pre>
     * Example 1:
     * 1) discount          : site: 1, group: 2
     * 2) discount          : time_from: 5, time_to: 8
     * Result:
     * 1) discount          : site: 1, group: 2, time_to: 4
     *    discount          : site: 1, group: 2, time_from: 9
     *    discount          : time_from: 5, time_to: 8
     *
     * Example 2:
     * 1) discount          : time_from: 5, time_to: 20
     * 2) discount+discount : time_from: 10, time_to: 15
     * Result:
     * 1) discount          : time_from: 5, time_to: 9
     *    discount          : time_from: 16, time_to: 20
     * 2) discount+discount : time_from: 10, time_to: 15
     * </pre>
     *
     * @param array $rules
     * @return array
     */
    protected function _cleanupDuplicateRules(array $rules)
    {
        $processedIds = array();
        foreach ($rules as $discountIds => $discountRules) {
            $processedIds[] = (string) $discountIds;
            if (!isset($rules[$discountIds])) {
                continue;
            }
            $discountCount = count(explode('_', $discountIds));
            foreach ($rules[$discountIds] as $key => $rule) {
                foreach ($rules as $_discountIds => $_discountRules) {
                    if (in_array((string) $_discountIds, $processedIds)) {
                        continue;
                    }
                    if (!isset($rules[$_discountIds])) {
                        continue;
                    }
                    $_discountCount = count(explode('_', $_discountIds));
                    foreach ($rules[$_discountIds] as $_key => $_rule) {
                        if ($_rule['site_id'] != $rule['site_id']
                            || $_rule['customer_group_id']
                                != $rule['customer_group_id']) {

                            continue;
                        }

                        if (!isset($rules[$discountIds][$key])
                            || !isset($rules[$_discountIds][$_key])) {

                            continue;
                        }

                        $discountKey    = $discountIds;
                        $ruleKey        = $key;
                        $rule1          = $rules[$discountIds][$key];
                        $rule2          = $rules[$_discountIds][$_key];
                        if ($discountCount >= $_discountCount) {
                            $discountKey    = $_discountIds;
                            $ruleKey        = $_key;
                            $rule1          = $rules[$_discountIds][$_key];
                            $rule2          = $rules[$discountIds][$key];
                        }

                        if ($rule1['time_from'] >= $rule2['time_from']
                            && $rule1['time_from'] <= $rule2['time_to']) {

                            if ($rule1['time_to'] <= $rule2['time_to']) {
                                unset($rules[$discountKey][$ruleKey]);
                                if (!count($rules[$discountKey])) {
                                    unset($rules[$discountKey]);
                                }
                            } else {
                                $rules[$discountKey][$ruleKey]['time_from'] =
                                    $rule2['time_to'] + 1;
                            }
                        } elseif ($rule1['time_from'] <= $rule2['time_from']
                            && $rule1['time_to'] >= $rule2['time_from']) {

                            if ($rule1['time_to'] <= $rule2['time_to']) {
                                $rules[$discountKey][$ruleKey]['time_to'] =
                                    $rule2['time_from'] - 1;
                            } else {
                                $rules[$discountKey][$ruleKey]['time_to'] =
                                    $rule2['time_from'] - 1;
                                $rules[$discountKey][] = array_merge(
                                    $rule1,
                                    array(
                                        'time_from' => $rule2['time_to'] + 1,
                                        'time_to'   => $rule1['time_to']
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }
        return $rules;
    }
}