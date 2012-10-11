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
class Axis_Checkout_Model_Cart extends Axis_Db_Table
{
    protected $_name = 'checkout_cart';

    /**
     * Return customer cart
     *
     * @param int $customerId
     * @return Axis_Checkout_Model_Cart
     */
    public function getCustomerCart($customerId)
    {
        $row = $this->select()
            ->where('customer_id = ?', $customerId)
            ->where('site_id = ?', Axis::getSiteId())
            ->fetchRow()
        ;
        if (!$row) {
           return null;
        }
        $this->setCartId($row->id);

        return $this;
    }

    /**
     * Initialize new cart
     *
     * @return int
     */
    protected function _initCartId()
    {
        $select = $this->select();

        $sessionCartId = Axis::session()->cartId;
        $customerId    = Axis::getCustomerId();
        $siteId        = Axis::getSiteId();
        $row           = null;

        if (null !== $sessionCartId) {
            $row = $select->where('id = ?', $sessionCartId)
                ->where('site_id = ?', $siteId)
                ->fetchRow();
        } elseif (null !== $customerId) {
            $row = $select->where('customer_id = ?', $customerId)
                ->where('site_id = ?', $siteId)
                ->fetchRow();
        }

        if (!$row) {
            $row = $this->createRow(array(
                'site_id'     => $siteId,
                'customer_id' => $customerId ? $customerId : new Zend_Db_Expr('NULL')
            ));
            $row->save();
        }

        $this->setCartId($row->id);

        return $row->id;
    }


    public function unsetCartId()
    {
        Axis::session()->cartId = null;
        return $this;
    }

    /**
     *
     * @param int $id
     * @return void
     */
    public function setCartId($id)
    {
        Axis::session()->cartId = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getCartId()
    {
        if (empty(Axis::session()->cartId)) {
            $this->_initCartId();
        }
        return Axis::session()->cartId;
    }

     /**
      *
      * @param int $productId
      * @param array $attributes
      * @param int $shoppingCartId [option]
      * @return mixed array|bool
      */
    private function _getClon($productId, $attributes, $shoppingCartId = null)
    {
        if (null === $shoppingCartId) {
            $shoppingCartId = $this->getCartId();
        }

        $statement = Axis::single('checkout/cart_product')
            ->select(array('id', 'quantity'))
            ->joinLeft(
                'checkout_cart_product_attribute',
                'ccpa.shopping_cart_product_id = ccp.id',
                array('attributeId' => 'product_attribute_id',
                    'product_attribute_value'
                )
            )
            ->where('ccp.shopping_cart_id = ?', $shoppingCartId)
            ->where('ccp.product_id = ?', $productId)
            ->query();

        $items = array();
        while ($row = $statement->fetch()) {
            if (!isset($items[$row['id']])) {
                $items[$row['id']] = array(
                    'quantity' => $row['quantity'],
                    'attributes' => array()
                );
            }
            if (!isset($row['attributeId'])) {
                continue;
            }
            $items[$row['id']]['attributes'][$row['attributeId']] =
                $row['product_attribute_value'];
        }

        foreach ($items as $itemId => $item) {
            if ((count($item['attributes']) == count($attributes))
                && !sizeof(array_diff_assoc($item['attributes'], $attributes)))
            {
                return array('id' => $itemId, 'quantity' => $item['quantity']);
            }
        }
        return false;
    }

    /**
     * Add new product or product variation to shopping cart
     *
     * @param int $productId
     * @param array $modifierOptions
     * @param array $variationOptions
     * @param array $quantity [optional]
     * @return mixed int|bool
     */
    public function add($productId, $modifierOptions, $variationOptions, $quantity = false)
    {
        $product = Axis::single('catalog/product')
            ->find($productId)
            ->current();

        if (!$product instanceof Axis_Catalog_Model_Product_Row) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Product not found'
                )
            );
            return false;
        }

        $stockRow = $product->getStockRow();

        if (!$quantity) {
            $quantity = $stockRow->min_qty_allowed ? $stockRow->min_qty_allowed : 1;
        }

        if (!$stockRow->decimal) {
            $quantity = floor($quantity);
        }

        $shoppingCartProductRow = array(
            'shopping_cart_id'     => $this->getCartId(),
            'product_id'           => $productId,
            'quantity'             => $quantity,
            'final_price'          => 0,
            'final_weight'         => 0,
            'variation_id'         => 0
        );

        $variationId = $product->getVariationIdByVariationOptions(
            $variationOptions
        );

        if (false === $variationId) {
            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    'Fill all variation options please'
                )
            );
            return false;
        }
        $shoppingCartProductRow['variation_id'] = $variationId;

        if (!$stockRow->canAddToCart($quantity, $variationId)) {
            return false;
        }

        $modifierAttributes = Axis::single('catalog/product_attribute')
            ->getAttributesByModifiers($product->id, $modifierOptions);
        if (false === $modifierAttributes) {
            return false;
        }

        $variationAttributes = Axis::single('catalog/product_attribute')
            ->getAttributesByVariation($variationId, $variationOptions);
        if (false === $variationAttributes) {
            return false;
        }

        $attributes = $modifierAttributes + $variationAttributes;

        Axis::message()->addSuccess(
            Axis::translate('checkout')->__(
                'Product was successfully added to your shopping cart'
            )
        );

        // Check for clon exists
        if (false !== ($clon = $this->_getClon($productId, $attributes))) {
            $this->updateItem(
                $clon['id'],
                $clon['quantity'] + Axis_Locale::getNumber($quantity)
            );
            return true;
        }

        // Insert product
        $shoppingCartProductRow['final_price']  =
            $product->getPrice(array_keys($attributes));
        $shoppingCartProductRow['final_weight'] =
            $product->getWeight(array_keys($attributes));

        $shoppingCartProductId = Axis::single('checkout/cart_product')
            ->insert($shoppingCartProductRow);

        // Insert attributes for this product
        $modelCartAttribute = Axis::single('checkout/cart_product_attribute');
        foreach ($attributes as $attributeId => $attributeValue) {
            $modelCartAttribute->insert(array(
                'shopping_cart_product_id' => $shoppingCartProductId,
                'product_attribute_id'     => $attributeId,
                'product_attribute_value'  => $attributeValue
            ));
        }

        Axis::dispatch('checkout_cart_add_product_success', array(
            'product'         => $product,
            'attributes'      => $attributes,
            'quantity'        => $quantity,
            'cart_product_id' => $shoppingCartProductId
        ));

        return $shoppingCartProductId;
    }

    /**
     * Updates quantity of cart product row.
     * !$itemId in not a product_id!
     *
     * @param int $itemId Id of cart product row
     * @param int $quantity
     * @return mixed bool|void
     */
    public function updateItem($itemId, $quantity)
    {
        $item = Axis::single('checkout/cart_product')
            ->find($itemId)
            ->current();

        if (!$item || $item->shopping_cart_id != $this->getCartId()) {
            return;
        }

        if (!$product = Axis::single('catalog/product')
                ->find($item->product_id)->current()) {

            $item->delete();
            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    "Product '%s' is not found in stock. product_id = %s",
                    $product->sku,
                    $item->product_id
                )
            );
            return false;
        }

        if ($quantity == 0) {
            $item->delete();
            return true;
        }

        $stockRow = $product->getStockRow();

        if (!$stockRow->canAddToCart($quantity, $item->variation_id)) {
            if ($stockRow->in_stock
                && ($quantityAvailable = $stockRow->getQuantity($item->variation_id, true))
                && $quantityAvailable >= $stockRow->min_qty_allowed) {

                $item->quantity = $quantityAvailable;

                if ($quantity < $stockRow->min_qty_allowed) {
                    $item->quantity = $stockRow->min_qty_allowed;
                }

                if ($stockRow->max_qty_allowed > 0
                    && $quantity > $stockRow->max_qty_allowed) {

                    if ($quantityAvailable > $stockRow->max_qty_allowed) {
                        $item->quantity = $stockRow->max_qty_allowed;
                    } else {
                        $item->quantity = $quantityAvailable;
                    }
                }

                $item->save();

                if ($quantity > $quantityAvailable) {
                    Axis::message()->addError(
                        Axis::translate('checkout')->__(
                            "Only %s item(s) of '%s' are available",
                            $quantityAvailable,
                            $product->sku
                        )
                    );
                }
            } else {
                $item->delete();
                Axis::message()->addError(
                    Axis::translate('checkout')->__(
                        "Product '%s' is out of stock",
                        $product->sku
                    )
                );
            }
            return false;
        }

        if (!$stockRow->decimal) {
            $quantity = floor($quantity);
        }

        Axis::dispatch('checkout_cart_update_product_success', array(
            'product' => $product,
            'quantity' => $quantity,
            'cart_product_id' => $itemId
        ));

        $item->quantity = $quantity;
        $item->save();
    }

    /**
     * @param int $itemId Id of cart_product_row (it's not a product_id)
     * @return mixed
     */
    public function deleteItem($itemId)
    {
        $row = Axis::model('checkout/cart_product')
            ->find($itemId)
            ->current();

        if (!$row || $row->shopping_cart_id != $this->getCartId()) {
            return false;
        }

        return $row->delete();
    }

    /**
     * Returns product list
     *
     * @return array mixed
     */
    public function getProducts($shoppingCartId = null)
    {
        if (null === $shoppingCartId) {
            $shoppingCartId = $this->getCartId();
        }
        return Axis::single('checkout/cart_product')->getProducts(
            $shoppingCartId
        );
    }

    /**
     * Validates cart content.
     * Write error messages to Axis_Message
     *
     * @return boolean
     */
    public function validateContent()
    {
        $isValid        = true;
        $productIds     = array();
        $cartProducts   = $this->getProducts();
        foreach ($cartProducts as $cartProduct) {
            $productIds[$cartProduct['id']] = $cartProduct['product_id'];
        }
        $products   = Axis::model('catalog/product')->find($productIds);

        $loadedProductIds = array();
        foreach ($products as $product) {
            $loadedProductIds[] = $product->id;
            $cartProductId      = array_search($product->id, $productIds);
            $cartProductRow     = $cartProducts[$cartProductId];

            $stockRow = $product->getStockRow();
            if (!$stockRow->canAddToCart(
                    $cartProductRow['quantity'],
                    $cartProductRow['variation_id'])) {

                $isValid = false;
                if ($stockRow->in_stock
                    && $quantity = $stockRow->getQuantity(
                        $cartProductRow['variation_id'],
                        true)
                    ) {

                    $this->updateItem($cartProductRow['id'], $quantity);
                    Axis::message()->addError(
                        Axis::translate('checkout')->__(
                            "Only %s item(s) of '%s' are available",
                            $quantity,
                            $cartProductRow['name']
                        )
                    );
                } else {
                    $this->deleteItem($cartProductRow['id']);
                    Axis::message()->addError(
                        Axis::translate('checkout')->__(
                            "Product '%s' is out of stock",
                            $cartProductRow['name']
                        )
                    );
                }
            }
        }

        foreach (array_diff($productIds, $loadedProductIds) as $missedProductId) {
            // shopping cart has deleted products
            $isValid        = false;
            $cartProductId  = array_search($missedProductId, $productIds);
            $cartProductRow = $cartProducts[$cartProductId];
            $this->deleteItem($cartProductRow['id']);
            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    "Product '%s' is not found in stock. product_id = %s",
                    $cartProductRow['name'],
                    $cartProductRow['product_id']
                )
            );
        }

        if (!$isValid) {
            Axis::message()->addNotice(
                Axis::translate('checkout')->__(
                    'Due errors while checking your shopping cart contents, we made some changes to it. Check it out now'
                )
            );
        }
        return $isValid;
    }

    /**
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return Axis::single('checkout/cart_product')
            ->select('SUM(quantity*final_price)')
            ->where('shopping_cart_id = ?', $this->getCartId())
            ->fetchOne();
    }

    /**
     *
     * @return float
     */
    public function getTotalWeight()
    {
        return Axis::single('checkout/cart_product')
            ->select('SUM(quantity*final_weight)')
            ->where('shopping_cart_id = ?', $this->getCartId())
            ->fetchOne();
    }

    /**
     * Return count shopping cart items
     *
     * @return int
     */
    public function getCount()
    {
        $count = 0;
        $items = Axis::single('checkout/cart_product')
            ->getProducts($this->getCartId());
        foreach ($items as $item) {
            if ($item['decimal']) {
                $count++;
            } else {
                $count += (int)$item['quantity'];
            }
        }
        return $count;
    }

    /**
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (0 === $this->getCount());
    }

    /**
     * Return customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        $customerId = $this->getCustomerIdById(
            $this->getCartId()
        );
        return $customerId ? $customerId : 0;
    }

    /**
     * Merge shopping carts
     *
     * @return bool
     */
    public function merge()
    {
        $siteId = Axis::getSiteId();
        if (!$customerId = Axis::getCustomerId()) {
            return false;
        }
        $adapter            = $this->getAdapter();
        $previousCartRow    = $this->select()
            ->where('site_id = ?', $siteId)
            ->where('customer_id = ?', $customerId)
            ->fetchRow();

        if ($previousCartRow && $previousCartRow->id != $this->getCartId()) {
            $previousCartProducts = Axis::model('checkout/cart_product')
                ->select(array('*', 'checkout_cart_product_id' => 'id'))
                ->joinLeft('checkout_cart_product_attribute',
                    'ccp.id = ccpa.shopping_cart_product_id',
                    '*'
                )->where('ccp.shopping_cart_id = ?', $previousCartRow->id)
                ->fetchAll();

            $result = array();
            foreach ($previousCartProducts as $p) {
                if (!isset($result[$p['checkout_cart_product_id']])) {
                    $result[$p['checkout_cart_product_id']] = array(
                        'product_id' => $p['product_id'],
                        'quantity' => $p['quantity']
                    );
                }
                $productAttributeId = $p['product_attribute_id'];
                if (!empty($productAttributeId)) {
                    $result[$p['checkout_cart_product_id']]['attributes'][$productAttributeId] =
                        $p['product_attribute_value']
                    ;
                }
            }
            $removedShoppingProductIds = array();
            foreach ($result as $shopppingCartProductId => $product) {

                $attributes = isset($product['attributes']) ?
                    $product['attributes'] : array();
                $clon = $this->_getClon($product['product_id'], $attributes);
                if (!$clon) {
                    continue;
                }
                $this->updateItem(
                    $clon['id'], $clon['quantity'] + $product['quantity']
                );
                $removedShoppingProductIds[] = $shopppingCartProductId;
            }
            if (count($removedShoppingProductIds)) {
                Axis::single('checkout/cart_product')->delete(
                    $adapter->quoteInto('id IN (?)', $removedShoppingProductIds)
                );
            }
            Axis::single('checkout/cart_product')->update(
                array(
                    'shopping_cart_id' => $this->getCartId()
                ),
                $adapter->quoteInto('shopping_cart_id = ?', $previousCartRow->id)
            );
            $this->delete(array(
                'site_id = ?'     => $siteId,
                'customer_id = ?' => $customerId
            ));
        }
        $this->update(
            array('customer_id' => $customerId),
            $adapter->quoteInto('id = ?', $this->getCartId())
        );
        return true;
    }

    /**
     * Remove all products from cart
     *
     * @return int
     */
    public function clear()
    {
        return Axis::single('checkout/cart_product')->delete(
            'shopping_cart_id = ' . (int) $this->getCartId()
        );
    }
}