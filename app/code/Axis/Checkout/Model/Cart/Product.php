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
 * @copyright   Copyright 2008-2010 Axis
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
        $select = $this->getAdapter()->select()
            ->from(array('scp' => $this->_prefix . 'checkout_cart_product'))
            ->where('scp.shopping_cart_id = ?', $shoppingCartId);
        
        if (!$this->select()->where('shopping_cart_id = ?', $shoppingCartId)->fetchRow()) {
            return $this->getAdapter()->fetchAll($select);
        }
        
        $languageId = Axis_Locale::getLanguageId();

        $select->joinLeft(array('p' => $this->_prefix . 'catalog_product'),
            'p.id = scp.product_id', array('sku', 'price', 'image_thumbnail', 'image_listing', 'image_base'))
            ->joinLeft(array('pd' => $this->_prefix . 'catalog_product_description'), 
                'pd.product_id = scp.product_id AND pd.language_id = ' . $languageId,
                array('name', 'description', 'image_seo_name'))
            ->joinLeft(array('hu' => $this->_prefix . 'catalog_hurl'),
                'hu.key_id = p.id AND hu.key_type = "p"',
                'key_word')
            ->joinLeft(array('ps' => $this->_prefix . 'catalog_product_stock'),
                'ps.product_id = p.id', 
                'decimal')
            ->joinLeft(array('scpa' => $this->_prefix . 'checkout_cart_product_attribute'),
                'scpa.shopping_cart_product_id = scp.id',
                array('shoppingCartProductAttributeId' => 'id', 'product_attribute_value'))
            ->joinLeft(array('pa' => $this->_prefix . 'catalog_product_attribute'),
                'pa.id = scpa.product_attribute_id',
                array())
            ->joinLeft(array('po' => $this->_prefix . 'catalog_product_option'),
                'po.id = pa.option_id',
                'input_type')
            ->joinLeft(array('pot' => $this->_prefix . 'catalog_product_option_text'),
                'pot.option_id = pa.option_id AND pot.language_id = ' . $languageId,
                array('option_name' => 'name'))
            ->joinLeft(array('povt' => $this->_prefix . 'catalog_product_option_value_text'),
                'povt.option_value_id = pa.option_value_id AND povt.language_id = ' . $languageId,
                array('value_name' => 'name'))
            ->order(array('scp.product_id', 'po.id'));
            
        $products = array();
        $productIds = array();
        foreach ($this->getAdapter()->fetchAll($select) as $row) {
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
        return $products;
    }
    
    /**
     * Retrieve the list of products with minimal using of joins
     * Returns cart product rows with decimal flag. Used to calculate products count
     * 
     * @param id $shoppingCartId
     * @return array
     */
    public function getProductsSimple($shoppingCartId)
    {
        return $this->select('*')
            ->joinLeft(
                'catalog_product_stock',
                'cps.product_id = ccp.product_id',
                'decimal'
            )
            ->where('ccp.shopping_cart_id =  ? ', $shoppingCartId)
            ->query()
            ->fetchAll();
    }
}