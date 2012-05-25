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
class Axis_Discount_Model_Discount extends Axis_Db_Table
{
    protected $_name = 'discount';

    protected $_primary = 'id';

    protected $_rowClass = 'Axis_Discount_Model_Discount_Row';

    protected $_selectClass = 'Axis_Discount_Model_Discount_Select';


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
     *
     * @param int $productId
     * @return array
     */
    public function getRulesByProduct($productId, $variationId = false)
    {
        $product = Axis::single('catalog/product')->find($productId)->current();

        $categories = Axis::single('catalog/product_category')->select('category_id')
            ->where('product_id = ?', $productId)
            ->fetchCol()
            ;
        ////////////////////////////////////////////////////////////////////////
        $collection = new Axis_Discount_Model_Discount_Collection();
        $collection
            ->addIsActiveFilter()
            ->addDateFilter()
            ->addSiteFilter(        Axis::getSiteId())
            ->addGroupFilter(       Axis::single('account/customer')->getGroupId())
            ->addManufactureFilter( $product->manufacturer_id)
            ->addCategoryFilter(    $categories)
            ->addProductIdFilter(   $product->id)
//            ->load()
            ;
        ////////////////////////////////////////////////////////////////////////
        $select = Axis::single('catalog/product_variation')->select()
            ->where('product_id = ? OR product_id IS NULL', $productId)
            ->order('id')
            ;
        if (false !== $variationId) {
            $select->where('id = ?', $variationId);
        }
        $variations = $select->fetchAssoc();
        ////////////////////////////////////////////////////////////////////////
        $attributes = array();
        $rowset = Axis::single('catalog/product_attribute')->select()
            ->where('product_id = ?', $product->id)
            ->fetchRowset()
            ;
        foreach ($rowset as $row) {
            $attributes[$row->variation_id][$row->id] = array(
                'optionId'       => $row->option_id,
                'optionValueId'  => $row->option_value_id
            );
        }
        $baseVariationAttributes = isset($attributes[0]) ? $attributes[0] : array();
        ////////////////////////////////////////////////////////////////////////
        $data = array();

        foreach ($variations as $variation) {

            // $variation->getPrice()
            $price = $this->_price(
                $product->price,
                $variation['price'],
                $variation['price_type']
            );
            $collection->addPriceFilter($price);

            // $variation->getAttributes()
            $variationAttributes = isset($attributes[$variation['id']]) ?
                $attributes[$variation['id']] : array();
            $variationAttributes = $baseVariationAttributes + $variationAttributes;

            $rules = $collection->load();
            foreach ($rules as $ruleId => $rule) {

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

        if (false === $variationId) {
            return isset($data[$productId]) ? $data[$productId] : array();
        }

        return isset($data[$productId][$variationId]) ?
            $data[$productId][$variationId] : array();
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
     * Returns discounts that can be applied to recieved products
     * Used for price index generation
     *
     * @param array $productIds             Products to search discounts for
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
    public function getApplicableDiscounts(array $productIds)
    {
        ////////////////////////////////////////////////////////////////////////
        $rowset = Axis::single('catalog/product_category')->select()
            ->where('product_id IN(?)', $productIds)
            ->fetchRowset();

        $categories = array();
        foreach ($rowset as $row) {
            $categories[$row->product_id][] = $row->category_id;
        }
        ////////////////////////////////////////////////////////////////////////
        $rowset = Axis::single('catalog/product_attribute')->select()
            ->where('product_id IN(?)', $productIds)
            ->fetchRowset()
            ;
        $attributes = array();
        foreach ($rowset as $row) {
            $attributes[$row->product_id][$row->variation_id][$row->id] = array(
                'optionId'       => $row->option_id,
                'optionValueId'  => $row->option_value_id
            );
        }
        ////////////////////////////////////////////////////////////////////////
        $data = array();

        $collection = new Axis_Discount_Model_Discount_Collection();
        $collection->addIsActiveFilter();

        $products = Axis::model('catalog/product')->find($productIds);
        foreach ($products as $product) {
            if (empty($categories[$product->id])) {
                continue;
            }

            $collection
                ->addProductIdFilter($product->id)
                ->addManufactureFilter($product->manufacturer_id)
                ->addCategoryFilter($categories[$product->id])
                ->addPriceFilter($product->price);

            $baseVariationAttributes = isset($attributes[$product->id][0]) ? $attributes[$product->id][0] : array();

            $rules = $collection->load();
            foreach ($rules as $ruleId => $rule) {

                if (!$this->_getAppliedAttributes(
                        $rule, $baseVariationAttributes)) {

                    continue;
                }

                $data[$product->id][$ruleId] = $rule;
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
        $collection = new Axis_Discount_Model_Discount_Collection();
        $rules = $collection
            ->addSpecialFilter()
            ->addProductIdFilter($productId)
            ->load()
        ;
        $rule = current($rules);

        if (!$rule) {
            return array();
        }

        $row = $this->find($rule['id'])->current();

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