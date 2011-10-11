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
class Axis_Catalog_Model_Product_Attribute extends Axis_Db_Table
{
    protected $_name = 'catalog_product_attribute';
    protected $_rowClass = 'Axis_Catalog_Model_Product_Attribute_Row';
//    protected $_selectClass = 'Axis_Catalog_Model_Product_Attribute_Select';
    protected $_dependentTables = array(
        'Axis_Catalog_Model_Product_Attribute_Value'
    );
    protected $_referenceMap = array(
        'Product' => array(
            'columns'       => 'product_id',
            'refTableClass' => 'Axis_Catalog_Model_Product',
            'refColumns'    => 'id',
            'onDelete'      => self::CASCADE
        ),
        'Variation' => array(
            'columns'       => 'variation_id',
            'refTableClass' => 'Axis_Catalog_Model_Product_Variation',
            'refColumns'    => 'id',
            'onDelete'      => self::CASCADE
        ),
        'Option' => array(
            'columns'       => 'option_id',
            'refTableClass' => 'Axis_Catalog_Model_Product_Option',
            'refColumns'    => 'id',
            'onDelete'      => self::CASCADE
        )
    );

    /**
     *
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        if (empty($data['weight'])) {
            $data['weight'] = '0';
        }
        if (empty($data['price'])) {
            $data['price'] = '0';
        }
        return parent::insert($data);
    }

    /**
     * Return options that are used in some product.
     * Each row of returned rowset is Axis_Catalog_Model_Product_Option_Row
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getUsedOptions()
    {
        $optionIds = $this->select(
            new Zend_Db_Expr('DISTINCT `option_id`')
        )->fetchCol();
        return Axis::single('catalog/product_option')->find($optionIds);
    }

    /**
     *
     * @param array $productIds
     * @return array
     */
    public function getAtrributesByProductIds(array $productIds)
    {
        $result = array();
        $rowset = $this->fetchAll(
            $this->getAdapter()->quoteInto('product_id IN(?)', $productIds)
        );
        foreach ($rowset as $row) {
            $result[$row->product_id][$row->variation_id][$row->id] =
                array(
                    'optionId' => $row->option_id,
                    'optionValueId' => $row->option_value_id
                );
        }
        return $result;
    }

    /**
     * Retrieve comparable attributes by productIds
     *
     * @param array $productIds
     * @param integer $languageId [optional]
     * @return array
     * <pre>
     * array(
     *  product_id => array(
     *      option_id => array(
     *          name => string,
     *          values => array of strings
     *      )
     *  )
     * )
     * </pre>
     */
    public function getComparable($productIds, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        $select = $this->select(array(
                'product_id',
                'value' => 'IF (cpovt.name !=\'\', cpovt.name, cpav.attribute_value)'
            ))
            ->joinInner('catalog_product_option',
                'cpa.option_id = cpo.id',
                array('option_id' => 'id'))
            ->joinInner('catalog_product_option_text',
                'cpot.option_id = cpo.id',
                array('name'))
            ->joinLeft('catalog_product_option_value_text',
                'cpovt.option_value_id = cpa.option_value_id AND cpovt.language_id = cpot.language_id')
            ->joinLeft('catalog_product_attribute_value',
                'cpav.product_attribute_id = cpa.id AND (cpav.language_id = cpot.language_id OR cpav.language_id = 0)')
            ->where('cpot.language_id = ?', $languageId)
            ->where('cpo.comparable = 1')
            ->where('cpa.product_id IN (?)', $productIds)
            ->order('cpo.sort_order ASC');

        $result = array();
        foreach ($select->fetchAll() as $attribute) {
            if (!isset($result[$attribute['product_id']][$attribute['option_id']])) {
                $result[$attribute['product_id']][$attribute['option_id']] = array(
                    'name'   => $attribute['name'],
                    'values' => array()
                );
            }
            $result[$attribute['product_id']][$attribute['option_id']]['values'][] = $attribute['value'];
        }

        return $result;
    }

    //@todo move to select
    protected function _prepareToOptionsArray($rowset)
    {
        $options = array();
        foreach ($rowset as $row) {
            $optionId = $row['option_id'];
            if (!isset($options[$optionId])) {

                $options[$optionId] = array(
                    'id'           => $optionId,
                    'type'         => $row['input_type'],
                    'attribute_id' => $row['id'],
                    'values'       => array()
                );
            }
            $options[$optionId]['values'][] = array(
                'id'           => $row['option_value_id'],
                'attribute_id' => $row['id'],
                'price'        => $row['price'],
                'price_type'   => $row['price_type']
            );
        }
        return $options;
    }

    protected function _hasRequiredAttibutes($modifierOptions , $options)
    {
        //validate
        $receivedModifierOptionIds = isset($options['id']) ?
            array_keys($options['id']) : array();
        if (isset($options['value'])) {
            $receivedModifierOptionIds = array_merge(
                $receivedModifierOptionIds, array_keys($options['value'])
            );
        }
        $notReceivedModifierOptionIds = array_diff(
            array_keys($modifierOptions), $receivedModifierOptionIds
        );

        foreach ($notReceivedModifierOptionIds as $optionId) {

            //if checkbox not checked
            if (Axis_Catalog_Model_Product_Option::TYPE_CHECKBOX
                == $modifierOptions[$optionId]['type']) {

                continue;
            }
            $productOptionName = Axis::single('catalog/product_option_text')
                ->find($optionId, Axis_Locale::getLanguageId())
                ->current()
                ->name;

            Axis::message()->addNotice(
                Axis::translate('checkout')->__(
                    'Set required attribute: %s', $productOptionName
                )
            );
            return false;
        }
        return true;
    }

     /**
     *
     * @param array $modifiers
     * @param int $optionId
     * @param mixed $valueId [optional]
     * @return mixed
     */
    protected function _getAttributeIdByModifierOption($modifiers, $optionId, $valueId = null)
    {
        foreach ($modifiers as $modifier) {

            if ($modifier['id'] != $optionId) {
                continue;
            }
            if (null !== $valueId && count($modifier['values'])) {
                foreach ($modifier['values'] as $value) {
                    if ($valueId != $value['id']) {
                        continue;
                    }
                    return $value['attribute_id'];
                }
            } elseif (
                $modifier['type'] == Axis_Catalog_Model_Product_Option::TYPE_STRING
                || $modifier['type'] == Axis_Catalog_Model_Product_Option::TYPE_TEXTAREA
                || !$valueId) {

                return $modifier['attribute_id'];
            }

        }
        Axis::message()->addError(
            Axis::translate('checkout')->__(
                'Invalid modifier value recieved'
        ));
        return false;
    }

    /**
     *
     * @param array $modifierOptions
     * @param array $options
     * @return mixed array|bool
     */
    protected function _prepareAttributesByModifierOptions($modifierOptions, $options)
    {
        $attributes = array();

        if (isset($options['value'])) {

            foreach ($options['value'] as $optionId => $attributeValue) {
                if ('' == $attributeValue) {
                    continue; //fix textarea and text option type
                }
                $attributeId = $this->_getAttributeIdByModifierOption(
                    $modifierOptions, $optionId
                );
                if (false === $attributeId) {
                    return false;
                }
                $attributes[$attributeId] = $attributeValue;
            }
        }

        if (isset($options['id'])) {
            foreach ($options['id'] as $optionId => $valueIds) {
                if (!is_array($valueIds)) {
                    $valueIds = array($valueIds);
                }
                foreach ($valueIds as $valueId) {
                    $attributeId = $this->_getAttributeIdByModifierOption(
                        $modifierOptions, $optionId, $valueId
                    );
                    if (false === $attributeId) {
                        return false;
                    }
                    $attributes[$attributeId] = null;
                }
            }
        }
        return $attributes;
    }

    /**
     *
     * @param int $productId
     * @param array $options
     * @return array
     */
    public function getAttributesByModifiers($productId, $options)
    {
        $rowset = $this->select('*')
            ->joinInner(
                'catalog_product_option',
                'cpo.id = cpa.option_id',
                array('input_type', 'visible')
            )
            ->joinLeft('catalog_product_option_value',
                'cpov.id = cpa.option_value_id'
            )
            ->where('cpa.modifier = 1')
            ->where('cpa.product_id = ?', $productId)
            ->fetchAll()
            ;

        //prepare
        $modifierOptions = $this->_prepareToOptionsArray($rowset);

        //validate
        if (false === $this->_hasRequiredAttibutes($modifierOptions, $options)) {
            return false;
        }

        return $this->_prepareAttributesByModifierOptions(
            $modifierOptions, $options
        );
    }

    /**
     *
     * @param int $variationId
     * @param array $options
     * @param array $attributes
     * @return mixed
     */
    public function getAttributesByVariation($variationId, $options)
    {
        if (empty($variationId)) {
            return array();
        }
        $attributes = $variationAttributes = array();
        $variationAttributes = Axis::single('catalog/product_attribute')
            ->select()
//                ->where('product_id = ?', $this->id)
            ->where('variation_id = ?', $variationId)
            ->fetchAll();
        foreach ($options as $optionId => $valueId) {

            $attributeId = false;
            foreach ($variationAttributes as $attribute) {
                if ($optionId == $attribute['option_id']
                    && $valueId == $attribute['option_value_id']) {

                    $attributeId = $attribute['id'];
                }
            }

            if (!$attributeId) {
                Axis::message()->addError('Invalid variation recieved');
                return false;
            }
            $attributes[$attributeId] = null;
        }
        return $attributes;
    }

    public function getProperties($productId, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        return Axis::single('catalog/product_attribute')
            ->select(array(
                'option_id',
                'option_value_id',
                'value' => "IF (cpovt.name != '', cpovt.name, cpav.attribute_value)"
            ))
            ->joinInner(
                'catalog_product_option',
                'cpo.id = cpa.option_id',
                array('visible', 'code')
            )
            ->joinInner(
                'catalog_product_option_text',
                'cpot.option_id = cpa.option_id',
                array('name', 'description')
            )
            ->joinLeft(
                'catalog_product_option_value_text',
                "cpovt.option_value_id = cpa.option_value_id AND cpovt.language_id = $languageId"
             )
            ->joinLeft(
                'catalog_product_attribute_value',
                "cpav.product_attribute_id = cpa.id AND (cpav.language_id = $languageId OR cpav.language_id = 0)"
            )
            ->where('cpa.product_id = ?', $productId)
            ->where('cpa.variation_id = 0')
            ->where('cpa.modifier = 0')
            ->where('cpot.language_id = ?', $languageId)
            ->order('cpo.sort_order')
            ->fetchAll();
    }

    public function getModifiers($productId, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        return Axis::single('catalog/product_attribute')
            ->select('*')
            ->joinInner(
                'catalog_product_option',
                'cpo.id = cpa.option_id',
                array('input_type', 'visible', 'code')
            )
            ->joinInner(
                'catalog_product_option_text',
                'cpot.option_id = cpa.option_id',
                array('option_name' => 'name', 'option_description' => 'description')
            )
            ->joinLeft(
                'catalog_product_option_value',
                'cpov.id = cpa.option_value_id'
            )
            ->joinLeft(
                'catalog_product_option_value_text',
                "cpovt.option_value_id = cpa.option_value_id AND cpovt.language_id = $languageId",
                array('value_name' => 'name')
            )
            ->where('cpa.product_id = ?', $productId)
            ->where('cpot.language_id = ?', $languageId)
            ->where('cpa.modifier = 1')
            ->order('cpo.sort_order')
            ->order('cpov.sort_order')
            ->order('value_name')
            ->fetchAll();
    }

    public function getVariations($productId, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        return Axis::single('catalog/product_attribute')
            ->select('*')
            ->joinInner('catalog_product_option',
                'cpo.id = cpa.option_id',
                array('input_type', 'visible', 'code')
            )
            ->joinInner('catalog_product_option_text',
                'cpot.option_id = cpa.option_id',
                array(
                    'option_name' => 'name',
                    'option_description' => 'description'
                )
            )
            ->joinInner(
                'catalog_product_option_value',
                'cpov.id = cpa.option_value_id'
            )
            ->joinInner(
                'catalog_product_option_value_text',
                'cpovt.option_value_id = cpa.option_value_id',
                array('value_name' => 'name')
            )
            ->joinInner(
                'catalog_product_variation',
                'cpa.variation_id = cpv.id'
            )
            ->joinInner(
                'catalog_product_stock',
                'cpa.product_id = cps.product_id AND ((cps.backorder = 0 AND cpv.quantity > 0) OR (cps.backorder > 0))'
            )
            ->where('cpa.product_id = ?', $productId)
            ->where('cpa.variation_id > 0')
            ->where('cpot.language_id = ?', $languageId)
            ->where('cpovt.language_id = ?', $languageId)
            ->order('cpo.sort_order')
            ->order('cpov.sort_order')
            ->order('cpa.id')
            ->fetchAll();
    }


    public function getAttributesByAttributeIds($attributeIds)
    {
        if (!is_array($attributeIds)) {
            $attributeIds = array($attributeIds);
        }
        return Axis::single('catalog/product_attribute')
            ->select('*')
            ->joinInner(
                'catalog_product_option',
                'cpo.id = cpa.option_id',
                'sort_order'
            )
            ->joinLeft(
                'catalog_product_option_value',
                'cpov.id = cpa.option_value_id',
                array('sort_order2' => 'sort_order')
            )
            ->where('cpa.id IN(?)', $attributeIds)
            ->order('cpo.sort_order ASC')
            ->order('cpov.sort_order')
            ->fetchAll();
    }
}