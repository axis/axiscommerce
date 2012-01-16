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
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Model_Cart_Product extends Axis_Db_Table
{
    protected $_name = 'checkout_cart_product';
    protected $_rowClass = 'Axis_Checkout_Model_Cart_Product_Row';

    /**
     * Retrieve the list of products including product hurl, options and attributes
     *
     * @param id $shoppingCartId
     * @return array
     */
    public function getProducts($shoppingCartId)
    {
        $registryIndex = 'checkout/cart_products_' . $shoppingCartId;
        if (Zend_Registry::isRegistered($registryIndex)) {
            return Zend_Registry::get($registryIndex);
        }

        $select = $this->select('*')
            ->where('shopping_cart_id = ?', $shoppingCartId);
        if (!$select->fetchRow()) {
            return array();
        }

        $languageId = Axis_Locale::getLanguageId();
        $select->joinLeft('catalog_product',
                'cp.id = ccp.product_id',
                array('sku', 'price', 'image_thumbnail', 'image_listing', 'image_base')
            )->joinLeft('catalog_product_description',
                'cpd.product_id = ccp.product_id AND cpd.language_id = ' . $languageId,
                array('name', 'description', 'image_seo_name')
            )->joinLeft('catalog_hurl',
                'ch.key_id = cp.id AND ch.key_type = "p"',
                'key_word'
            )->joinLeft('catalog_product_stock',
                'cps.product_id = cp.id',
                'decimal'
            )->joinLeft('checkout_cart_product_attribute',
                'ccpa.shopping_cart_product_id = ccp.id',
                array('shoppingCartProductAttributeId' => 'id', 'product_attribute_value')
            )->joinLeft('catalog_product_attribute',
                'cpa.id = ccpa.product_attribute_id'
            )->joinLeft('catalog_product_option',
                'cpo.id = cpa.option_id',
                'input_type'
            )->joinLeft('catalog_product_option_text',
                'cpot.option_id = cpa.option_id AND cpot.language_id = ' . $languageId,
                array('option_name' => 'name')
            )->joinLeft('catalog_product_option_value_text',
                'cpovt.option_value_id = cpa.option_value_id AND cpovt.language_id = ' . $languageId,
                array('value_name' => 'name')
            )
            ->order(array('ccp.product_id', 'cpo.id'))
            ->limit(); //reset limit 1

        $products = array();
        $productIds = array();
        foreach ($select->fetchAll() as $row) {
            if (!isset($products[$row['id']])) {
                $productIds[$row['product_id']] = $row['product_id'];
                $products[$row['id']] = $row;
                $products[$row['id']]['final_price'] = (float) $row['final_price'];
                $products[$row['id']]['decimal']     = (bool) $row['decimal'];
                $products[$row['id']]['attributes']  = array();
            }
            if (!isset($row['shoppingCartProductAttributeId'])) {
                continue;
            }

            $attributte = array(
                'product_option' => $row['option_name']
            );
            switch ($row['input_type']) {
                case Axis_Catalog_Model_Product_Option::TYPE_SELECT:

                case Axis_Catalog_Model_Product_Option::TYPE_RADIO:
                    $attributte['product_option_value'] = $row['value_name'];
                    break;
                case Axis_Catalog_Model_Product_Option::TYPE_CHECKBOX:
                    $attributte['product_option_value'] = isset($row['value_name']) ?
                        $row['value_name'] : Axis::translate('checkout')->__('Checked');
                    break;
                default:
                    $attributte['product_option_value'] = $row['product_attribute_value'];
                    break;
            }

            $products[$row['id']]['attributes'][$row['shoppingCartProductAttributeId']]
                = $attributte;
        }

        $images = Axis::single('catalog/product_image')->getList($productIds);
        foreach ($products as $product) {
            $products[$product['id']]['images'] = $images[$product['product_id']];
        }

        Zend_Registry::set($registryIndex, $products);
        return Zend_Registry::get($registryIndex);
    }
}
