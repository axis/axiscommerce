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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Row extends Axis_Db_Table_Row
{
    /**
     * @var Axis_Catalog_Model_Product_Stock_Row
     */
    protected $_stockRow = null;

    public function save()
    {
        //before save
        if (empty($this->created_on)) {
            $this->created_on = Axis_Date::now()->toSQLString();
        }
        $this->modified_on = Axis_Date::now()->toSQLString();
        if (empty($this->date_available)) {
            $this->date_available = new Zend_Db_Expr('NULL');
        }
        if (empty($this->manufacturer_id)) {
            $this->manufacturer_id = new Zend_Db_Expr('NULL');
        }
        if (empty($this->ordered)) {
            $this->ordered = 0;
        }
        if (empty($this->viewed)) {
            $this->viewed = 0;
        }
        if (empty($this->tax_class_id)) {
            $this->tax_class_id = new Zend_Db_Expr('NULL');
        }

        return parent::save();
    }

    /**
     * Updates product to category assignments
     *
     * @param array $category
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setCategoryAssignments($category = null)
    {
        Axis::single('catalog/product_category')->delete(
            $this->getAdapter()->quoteInto('product_id IN(?)', $this->id)
        );
        if (null === $category) {
            return $this;
        }
        if (!is_array($category)) {
            $category = array($category);
        }
        foreach ($category as $id) {
            Axis::single('catalog/product_category')->insert(array(
                'category_id' => $id,
                'product_id'  => $this->id
            ));
        }
        return $this;
    }

    /**
     * Update product_stock table
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setStock($data = null)
    {
        if (null === $data) {
            return $this;
        }

        foreach ($data as $col => $val) {
            if (empty($val)) {
                $data[$col] = intval($data[$col]);
            }
        }
        $row = $this->getStockRow();
        $oldStockData = $row->toArray();
        $row->setFromArray($data);
        $row->save();
        Axis::dispatch('catalog_product_update_stock', array(
            'product'   => $this,
            'old_data'  => $oldStockData,
            'stock'     => $row
        ));
        return $this;
    }

    /**
     * Update product images
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setImage($data)
    {
        $mImage      = Axis::single('catalog/product_image');
        $mImageTitle = Axis::single('catalog/product_image_title');
        $languages   = Axis::model('locale/option_language')->toArray();

        $imageTypes     = array('base', 'listing', 'thumbnail');
        $updatedImages  = array();
        $imageIds       = array();
        foreach ($data as $image) {
            if (!isset($image['id'])
                || !$row = $mImage->find($image['id'])->current()) {

                $row = $mImage->createRow();
            }

            $row->setFromArray($image);
            $row->product_id = $this->id;

            if (isset($image['remove']) && $image['remove']) {
                $row->delete();
            } else {
                $row->save();
                $imageIds[$row->id] = $row->id;
                $mImageTitle->delete(
                    $this->getAdapter()->quoteInto('image_id = ? ', $row->id)
                );
                foreach ($languages as $languageId => $name) {
                    $mImageTitle->insert(array(
                        'image_id'      => $row->id,
                        'language_id'   => $languageId,
                        'title'         => isset($image['title_' . $languageId]) ?
                            $image['title_' . $languageId] : ''
                    ));
                }

                foreach ($imageTypes as $type) {
                    if ($image['is_' . $type]) {
                        $this->{'image_' . $type} = $row->id;
                        $updatedImages[$type] = true;
                    }
                }
            }
        }

        foreach ($imageTypes as $type) {
            // if base, listing or thumbnail image was not updated
            // while previously linked image with this type was recieved
            // we should unset this type of image
            if (empty($updatedImages[$type]) && in_array($this->{'image_' . $type}, $imageIds)) {
                $this->{'image_' . $type} = new Zend_Db_Expr('NULL');
            }
        }

        $this->save();
        return $this;
    }

    /**
     * Update related products assignment
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setRelated($data)
    {
        $mRelated = Axis::model('catalog/product_related');
        foreach ($data as $rowData) {
            $row = $mRelated->getRow($this->id, $rowData['related_product_id']);
            if (!$rowData['status']) {
                $row->delete();
            } else {
                $row->setFromArray($rowData)
                    ->save();
            }
        }
        return $this;
    }

    /**
     * Update discount on product
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setSpecial($data)
    {
        $existSpecialDiscountId = Axis::model('discount/eav')->select('discount_id')
            ->join('discount_eav', 'de.discount_id = de2.discount_id')
            ->where("de.entity = 'productId'")
            ->where('de.value = ?', $this->id)
            ->where("de2.entity ='special'")
            ->where('de2.value = 1')
            ->fetchOne();

        $mDiscount = Axis::model('discount/discount');
        if ($existSpecialDiscountId) {
            $mDiscount->delete('id = ' . $existSpecialDiscountId);
        }

        if (!empty($data['price'])) {
            $mDiscount->setSpecialPrice(
                $this->id,
                $data['price'],
                $data['from_date_exp'],
                $data['to_date_exp']
            );
        }
        return $this;
    }

    /**
     * Update new product status
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     * @deprecated Reason: use direct assignment instead
     */
    public function setNew($data)
    {
        $this->new_from = empty($data['from_date']) ?
            new Zend_Db_Expr('NULL') : $data['from_date'];
        $this->new_to = empty($data['to_date']) ?
            new Zend_Db_Expr('NULL') : $data['to_date'];
        $this->save();
        return $this;
    }

    /**
     * Updates product description
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setDescription($data)
    {
        $tableDesc = Axis::single('catalog/product_description');
        foreach (Axis::model('locale/option_language') as $languageId => $_n) {
            if (!$row = $tableDesc->find($this->id, $languageId)->current()) {
                $row = $tableDesc->createRow();
                $row->product_id = $this->id;
                $row->language_id = $languageId;
            }
            if (!empty($data[$languageId])) {
                $row->setFromArray($data[$languageId]);
            }
            $row->save();
        }
        return $this;
    }

    /**
     * Update variations
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setVariation($data)
    {
        $modelVariation = Axis::single('catalog/product_variation');
        $modelAttribute = Axis::single('catalog/product_attribute');
        foreach ($data as $variation) {
            if (!isset($variation['id'])
                || !$row = $modelVariation->find($variation['id'])->current()) {

                if ($variation['remove']) {
                    continue;
                }

                unset($variation['id']);
                $row = $modelVariation->createRow($variation);
                $row->product_id = $this->id;
                $row->save();
                foreach ($variation['attributes'] as $attribute) {
                    $modelAttribute->insert(array(
                        'variation_id'  => $row->id,
                        'product_id'            => $this->id,
                        'option_id'     => $attribute['option_id'],
                        'option_value_id' =>
                            isset($attribute['option_value_id']) ?
                                $attribute['option_value_id'] :
                                new Zend_Db_Expr('NULL')
                    ));
                }
            } elseif ($variation['remove']) {
                $row->delete();
            } else {
                $row->setFromArray($variation);
                $row->save();
            }
        }
        return $this;
    }

    /**
     * Update modifiers
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setModifier($data)
    {
        $mAttribute = Axis::single('catalog/product_attribute');
        foreach ($data as $modifier) {
            if (!isset($modifier['id'])
                || !$row = $mAttribute->find($modifier['id'])->current()) {

                $row = $mAttribute->createRow();
                $row->product_id = $this->id;
                $row->modifier = 1;
            }

            if ($modifier['remove']) {
                $row->delete();
            } else {
                $modifier['option_value_id'] = $modifier['option_value_id'] ?
                    $modifier['option_value_id'] : new Zend_Db_Expr('NULL');
                $row->setFromArray($modifier);
                $row->save();
            }
        }
        return $this;
    }

    /**
     * Update properties
     *
     * @param array $data
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setProperty($data)
    {
        $mAttribute = Axis::single('catalog/product_attribute');
        $mAttributeValue = Axis::single('catalog/product_attribute_value');
        $languages = Axis::model('locale/option_language')->toArray();

        foreach ($data as $property) {
            if (!isset($property['id'])
                || !$row = $mAttribute->find($property['id'])->current()) {

                unset($property['id']);
                $row = $mAttribute->createRow();
                $row->product_id = $this->id;
            }

            if ($property['remove']) {
                $row->delete();
            } else {
                $isInputable = !(bool) $property['option_value_id'];
                $property['option_value_id'] = $property['option_value_id'] ?
                    $property['option_value_id'] : new Zend_Db_Expr('NULL');
                $row->setFromArray($property);
                $row->save();

                if ($isInputable) {
                    if ($property['languagable']) {
                        $property['values'] = Zend_Json::decode($property['value_name']);
                        foreach ($languages as $languageId => $language) {
                            if (!isset($property['values']['value_' . $languageId])) {
                                continue;
                            }
                            if (!$values_row = $mAttributeValue->find($row->id, $languageId)->current()) {
                                $values_row = $mAttributeValue->createRow();
                                $values_row->product_attribute_id = $row->id;
                                $values_row->language_id = $languageId;
                            }
                            $values_row->attribute_value = $property['values']['value_' . $languageId];
                            $values_row->save();
                        }
                    } else {
                        if (!$values_row = $mAttributeValue->find($row->id, 0)->current()) {
                            $values_row = $mAttributeValue->createRow();
                            $values_row->product_attribute_id = $row->id;
                            $values_row->language_id = 0;
                        }
                        $values_row->attribute_value = $property['value_name'];
                        $values_row->save();
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Update human url
     *
     * @param string $url
     * @param array $siteIds
     * @return Axis_Catalog_Model_Product_Row
     */
    public function setUrl($url = '', $siteIds = null)
    {
        $url = trim($url);
        if (empty($url)) {
            $description = $this->getDescription(Axis_Locale::getLanguageId());
            $url = $description['name'];
        }

        if (empty($url)) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Please fill product url field'
                )
            );
            return $this;
        }

        // no strtolower to support non-latin urls
        $url = str_replace(
            array('\\', '/', ' '),
            Axis::config('catalog/product/hurldelimiter'),
            $url
        );

        if (null === $siteIds) { // completely unset link
            $siteIds = array();
        } elseif (!is_array($siteIds)) {
            $siteIds = array($siteIds);
        } elseif (!count($siteIds)) { // save link on product with no category assignments
            $siteIds = array(0);
        }

        $tableHurl = Axis::single('catalog/hurl');

        if (count($siteIds)) {
            if ($tableHurl->hasDuplicate($url, $siteIds, $this->id)) {
                Axis::message()->addError(
                    Axis::translate('catalog')->__(
                        'Product url not saved. Duplicate entry url'
                    )
                );
                return $this;
            }
        }

        $tableHurl->delete($this->getAdapter()->quoteInto(
            "key_id = ? AND key_type = 'p'", $this->id
        ));

        foreach ($siteIds as $siteId) {
            $tableHurl->save(array(
                'site_id' => $siteId,
                'key_id' => $this->id,
                'key_type' => 'p',
                'key_word' => $url
            ));
        }

        return $this;
    }

    /**
     * Retrieve array of category paths, where the product lies in
     *
     * @param int $preferableCategoryId defines which of product paths is more important (if product has 2 or more paths)
     * @param boolean $allPaths if true - function will return all paths of product
     * @return array
     * <pre>
     * Array(
     *     // path 1
     *     [0] => Array(
     *          Array (
     *             [lvl] => 1
     *             [key_word] => modern-style
     *             [id] => 59
     *             [name] => Modern Style
     *          )
     *          Array (
     *             [lvl] => 2
     *             [key_word] => inner-modern-style
     *             [id] => 60
     *             [name] => Inner Modern Style
     *          )
     *     )
     *     // path 2
     *     [1] => ...
     * )
     * </pre>
     */
    public function getParentItems(
        $preferableCategoryId = null, $allPaths = false, $languageId = null)
    {
        $rowset = Axis::single('catalog/category')->getRelatedCategoriesByProductId(
            $this->id, $languageId
        );

        $result = array();
        $i = $prevLvl = 0;
        $inactivePaths = array();
        foreach ($rowset as $category) {
            if ($prevLvl >= $category['lvl']) {
                $i++;
            }
            if ($category['status'] != 'enabled') {
                $inactivePaths[] = $i;
            }
            $result[$i][] = $category;
            $prevLvl = $category['lvl'];
        }

        if ($allPaths) {
            return $result;
        }

        // find most suitable path
        if (null !== $preferableCategoryId) {
            foreach ($result as $path) {
                foreach ($path as $category) {
                    if (in_array($preferableCategoryId, $category)) {
                        return $path;
                    }
                }
            }
        }

        // try to return path with active categories
        $limit = count($result);
        if (count($inactivePaths) < $limit) {
            $i = 0;
            do {
                if (!in_array($i, $inactivePaths)) {
                    return $result[$i];
                }
                $i++;
            } while ($i < $limit);
        }

        return current($result) ? current($result) : array();
    }

    /**
     * Check is product available to buy without variation.
     *
     * @return bool
     */
    public function isAvailable()
    {
        $stock = $this->getStockRow();

        if (!$stock->in_stock) {
            return false;
        }

        if (($stock->min_qty_allowed > $this->quantity ||
                $stock->min_qty >= $this->quantity)
              && !$stock->backorder) {

            return false;
        }

        return true;
    }

    /**
     * Check is product available to buy without or with variation
     *
     * @return bool
     */
    public function isSaleable()
    {
        if ($this->isAvailable()) {
            return true;
        }

        $variations = $this->findDependentRowset('Axis_Catalog_Model_Product_Variation');
        foreach ($variations as $variation) {
            if ($variation['quantity'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve description row for product
     *
     * @param int $languageId
     * @return array
     */
    public function getDescription($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        $row = Axis::single('catalog/product_description')->select('*')
            ->where('product_id = ?', $this->id)
            ->where('language_id = ?', $languageId)
            ->fetchRow()
            ;
        return $row ? $row->toArray() : false;
    }

    public function getProperties()
    {
        return Axis::single('catalog/product_attribute')
            ->getProperties($this->id);
    }

    public function getModifiers()
    {
        $rawModifiers = Axis::single('catalog/product_attribute')
            ->getModifiers($this->id);

        $modifiers = array();
        foreach ($rawModifiers as $row) {
            $optionId = $row['option_id'];
            if (!isset($modifiers[$optionId]))
                $modifiers[$optionId] = array(
                    'id'            => $optionId,
                    'name'          => $row['option_name'],
                    'code'          => $row['code'],
                    'description'   => $row['option_description'],
                    'type'          => $row['input_type'],
                    'visible'       => $row['visible'],
                    'attribute_id'  => $row['id'],
                    'values'        => array()
                );
            $modifiers[$optionId]['values'][] = array(
                'id'            => $row['option_value_id'],
                'name'          => $row['value_name'],
                'attribute_id'  => $row['id'],
                'price'         => $row['price'],
                'price_type'    => $row['price_type']
            );
        }
        return $modifiers;
    }

    public function getVariationAttributesData()
    {
       $rowset = Axis::single('catalog/product_attribute')
            ->getVariations($this->id);

        $optionsLabels = array();
        $valuesLabels  = array();
        $options       = array();
        $variations    = array();

//        while (($row = $rowset->fetch())) {
        foreach ($rowset as $row) {

            $otionId = $row['option_id'];
            $optionValueId = $row['option_value_id'];

            $optionsLabels[$otionId] = array(
                'name'          => $row['option_name'],
                'visible'       => $row['visible'],
                'description'   => $row['option_description']
            );
            $valuesLabels[$optionValueId]               = $row['value_name'];
            $options[$otionId][$optionValueId]          = $optionValueId;
            $variations[$row['variation_id']][$otionId] = $optionValueId;
        }
        return array(
            'variationsAssign' => $variations,
            'optionsAssign'    => $options,
            'optionsLabels'    => $optionsLabels,
            'valuesLabels'     => $valuesLabels
        );
    }

    public function getPriceRules()
    {
        $price = $this->price;
        $variations = Axis::single('catalog/product_variation')->select()
            ->where('product_id = ?', $this->id)
            ->fetchAll();
        $modifiers = Axis::single('catalog/product_attribute')->select()
            ->where('product_id = ?', $this->id)
            ->where('modifier = 1')
            ->where('variation_id = 0')
            ->fetchAll();

        $result = array(
            'price'        => $this->price,
            'finalPrice'   => $price,
            'currencyRate' => Axis::single('locale/currency')->getData(null, 'rate')
        );

        if (!count($variations) && !count($modifiers)) {
            return $result;
        }
        //$difference = array();

        foreach ($variations as $variation) {

            $diff = self::getNewPrice($price, $variation['price'], $variation['price_type']);
            $diff = $diff - $price;
            $amount = $variation['price'];

            //$difference[] = $diff;
            $result['variation'][$variation['id']] = array(
                'id'         => $variation['id'],
                'amount'     => $amount,
                'type'       => $variation['price_type'],
                'difference' => $diff
            );
        }

        foreach ($modifiers as $modifier) {
            $diff = self::getNewPrice($price, $modifier['price'], $modifier['price_type']);

            $diff = $diff - $price;
            $amount = $modifier['price'];

            //$difference[] = $diff;
            $result['modifier'][$modifier['id']] = array(
                'id'         => $modifier['id'],
                'optionId'   => $modifier['option_id'],
                'amount'     => $amount,
                'type'       => $modifier['price_type'],
                'difference' => $diff
            );
        }
        //$res['from'] = min($difference);
        //$res['to'] = max($difference);
        return $result;
    }

    /**
     * Get the product price including attributes and discounts
     *
     * @return float
     */
    public function getPrice($attributeIds = array())
    {
        $price = $this->price;
        if (!count($attributeIds)) {
            $discountRule = Axis::single('discount/discount')
                ->getRulesByProduct($this->id, 0);
            $price = Axis::single('discount/discount')->applyDiscountRule(
                $price, $discountRule
            );
            return $price;
        }

        $attributes = Axis::single('catalog/product_attribute')
            ->getAttributesByAttributeIds($attributeIds);

        $variationId = 0;
        $changeStack = array();
        // (//) => TO TO Kostul`
        $priceTo = array(); //
        foreach ($attributes as $attribute) {
            if ($attribute['variation_id']) {
                $variationId = $attribute['variation_id'];
            }
            if ($attribute['modifier']) {

                if ($attribute['price_type'] != 'to') {//

                    $changeStack[] = array(
                        'price'       => $attribute['price'],
                        'price_type'  => $attribute['price_type']
                    );
                } else {//
                    $priceTo[] = $attribute['price'];//
                }//
            }
        }

        // set variation
        if ($variationId) {
            $variation = Axis::single('catalog/product_variation')
                ->find($variationId)
                ->current();
            $price = self::getNewPrice(
                $price, $variation->price, $variation->price_type
            );
        }
        // set discount
        $discountRule = Axis::single('discount/discount')->getRulesByProduct(
            $this->id, $variationId
        );
        $price = Axis::single('discount/discount')->applyDiscountRule(
            $price, $discountRule, $attributeIds
        );

        // set max modifier to
        if (count($priceTo)) { //
            $price = self::getNewPrice($price, max($priceTo), 'to');//
        }//

        //apply "by" and "percent" modifier
        $modifierAmount = 0;
        foreach ($changeStack as $sort) {
            //foreach ($sort as $item) {
                $modifierAmount += - $price + self::getNewPrice(
                    $price, $sort['price'], $sort['price_type']
                );
            //}
        }
        $price += $modifierAmount;

        return  $price;
    }

    /**
     * Get the product weight considering on the attribute set
     *
     * @return float
     */
    public function getWeight($attributes = array())
    {
        $db = $this->getAdapter();

        $weight = $this->weight;

        if (!count($attributes)) {
            return $weight;
        }

        $attributes = Axis::single('catalog/product_attribute')
            ->select('*')
            ->where('id IN(?)', $attributes)
            ->fetchAll();

        $variationId = 0;
        $changeStack = array();
        foreach ($attributes as $attribute) {
            if ($attribute['variation_id']) {
                $variationId = $attribute['variation_id'];
            }
            if ($attribute['modifier']) {
                $changeStack[] = array(
                    'weight'      => $attribute['weight'],
                    'weight_type' => $attribute['weight_type']
                );
            }
        }
        if ($variationId) {
            $variation = Axis::single('catalog/product_variation')->select('*')
                ->where('id = ?', $variationId)
                ->fetchRow();

            $weight = self::getNewWeight(
                $weight, $variation->weight, $variation->weight_type
            );
        }

        foreach ($changeStack as $item) {
            $weight = self::getNewWeight(
                $weight, $item['weight'], $item['weight_type']
            );
        }

        return $weight;
    }

    /**
     * Retrieve the variations array with their attributes
     *
     * @return array
     * <pre>
     * array(
     *     variationId => array(optionId => valueId, optionId => valueId),
     *     variationId => array(optionId => valueId, optionId => valueId)
     * )
     * </pre>
     */
    public function getVariationsAssign()
    {
        $rowset = Axis::single('catalog/product_attribute')->select()
            ->where('product_id = ?', $this->id)
            ->where('variation_id > 0')
            ->fetchAll()
            ;

        $variations = array();
        foreach ($rowset as $row) {
            $variations[$row['variation_id']][$row['option_id']]
                = $row['option_value_id'];
        }
        return $variations;
    }

    /**
     *
     * @param array $options
     * <pre>
     * array(
     *  optionId => valueId,
     *  optionId => valueId
     * )
     * </pre>
     * @return int|bool
     */
    public function getVariationIdByVariationOptions($options)
    {
        /* Check for variation exists */
        foreach ($options as $optionId => $valueId) {
            if (!$valueId) {
                unset($options[$optionId]);
            }
        }
        if (empty($options)) {
            return 0;
        }
        $variations = $this->getVariationsAssign();
        $maxMatch = 0;
        $currentDiff = 0;
        $result = false;
        foreach ($variations as $variationId => $variation) {
            $match = count(array_intersect_assoc($variation, $options));
            $diff = count(array_diff_assoc($variation, $options));
            if ($maxMatch < $match) {
                $maxMatch = $match;
                $currentDiff = $diff;
                $result = $variationId;
            } elseif ($maxMatch == $match) {
                if ($diff == 0) {
                    $result = $variationId;
                } elseif ($currentDiff >= $diff) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     *
     * @return string
     */
    public function getHumanUrl()
    {
        return Axis::single('catalog/hurl')
            ->select()
            ->where('key_id = ?', $this->id)
            ->where("key_type = 'p'")
            ->fetchOne();
    }

    public function incViewed()
    {
        $productDescription = Axis::single('catalog/product_description')
            ->find($this->id, Axis_Locale::getLanguageId())
            ->current();

        if (isset($productDescription->viewed)) {
            $productDescription->viewed++;
            $productDescription->save();
        }
        $this->viewed++;
        return $this->save();
    }

    public function getImages()
    {
        return $this->findDependentRowset('Axis_Catalog_Model_Product_Image');
    }

    public function getAttributes()
    {
        return $this->findDependentRowset('Axis_Catalog_Model_Product_Attribute');
    }

    public function getQuantity($variationId = null, $availableOnly = false)
    {
        return $this->getStockRow()
            ->getQuantity($this->id, $variationId, $availableOnly);
    }

    /**
     *
     * @param int $quantity
     * @param int $variationId
     * @return bool
     */
    public function canAddToCart($quantity, $variationId = null)
    {
        return $this->getStockRow()
            ->canAddToCart($this->id, $quantity, $variationId);
    }

    /**
     * @return Axis_Catalog_Model_Product_Stock_Row
     */
    public function getStockRow()
    {
        if (!$this->_stockRow) {
            $mStock = Axis::model('catalog/product_stock');
            $this->_stockRow = $mStock->find($this->id)
                ->current();
            if (!$this->_stockRow) { // saving new product
                $this->_stockRow = $mStock->createRow();
                $this->_stockRow->product_id = $this->id;
            }
            $this->_stockRow->setProductRow($this);
        }
        return $this->_stockRow;
    }

    public function getCategories($languageId = null)
    {
        return Axis::single('catalog/product_category')
            ->getCategoriesByProductId($this->id, $languageId);
    }

    /**
     * @return mixed null|array
     */
    public function getManufacturer($languageId = null)
    {
        if (null === $this->manufacturer_id) {
            return null;
        }
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        return Axis::single('catalog/product_manufacturer')->select('*')
            ->joinLeft('catalog_product_manufacturer_description',
                'cpm.id = cpmd.manufacturer_id AND cpmd.language_id = ' . $languageId,
                '*')
            ->joinLeft('catalog_hurl',
                "cpm.id = ch.key_id AND ch.key_type = 'm' AND ch.site_id = " . Axis::getSiteId(),
                'key_word')
            ->where('cpm.id = ?', $this->manufacturer_id)
            ->fetchRow()
            ->toArray();
    }

    /**
     * @static
     * @param float $price
     * @param float $amount
     * @param string $amountType
     * @return float
     */
    public static function getNewPrice($price, $amount, $amountType)
    {
        $price  = floatval($price);
        $amount = floatval($amount);
        if ($amount == 0) {
            return $price;
        }
        switch ($amountType) {
            case 'to':
                return $amount;
            case 'by':
                return $price + $amount;
            case 'percent':
                return $price + ($price * $amount / 100);
            default:
                return $price;
        }
    }

    /**
     *
     * @static
     * @param float $weight
     * @param float $amount
     * @param string $amountType
     * @return float
     */
    public static function getNewWeight($weight, $amount, $amountType)
    {
        $weight = floatval($weight);
        $amount = floatval($amount);
        if ($amount == 0)
            return $weight;
        switch ($amountType) {
            case 'to':
                return $amount;
            case 'by':
                return $weight + $amount;
            case 'percent':
                return $weight + ($weight * $amount / 100);
            default:
                return $weight;
        }
    }
}