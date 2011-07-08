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
class Axis_Catalog_Model_Product_Stock_Row extends Axis_Db_Table_Row
{
    /**
     * @var Axis_Catalog_Model_Product_Row
     */
    protected $_productRow = null;

    /**
     * @param int $quantity [optional]
     * @param int $variationId [optional]
     * @param boolean $isBackOrdered [optional]
     * @return boolean
     */
    protected function _available($quantity = 1, $variationId = null, $isBackOrdered = false)
    {
        if (!$this->in_stock) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Product is out of stock'
                )
            );
            return false;
        }

        if ($quantity < $this->min_qty_allowed || $quantity <= 0) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Minimum allowed quantity is %d',
                    $this->min_qty_allowed > 0 ? $this->min_qty_allowed : 1
                )
            );
            return false;
        }

        if ($quantity > $this->max_qty_allowed && $this->max_qty_allowed > 0) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Maximum allowed quantity is %d', $this->max_qty_allowed
                )
            );
            return false;
        }

        $stockQuantity = $this->getQuantity($variationId);
        $availableQuantity = $stockQuantity - $this->min_qty;

        if ($isBackOrdered && ($quantity > $availableQuantity) && !$this->backorder) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Only %d item(s) are available',
                    $availableQuantity
                )
            );
            return false;
        }

        if (!$isBackOrdered && $quantity > $availableQuantity) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Only %d item(s) are available',
                    $availableQuantity
                )
            );
            return false;
        }
        return true;
    }

    /**
     * Checks, posibility to set pending status
     * on a product from shopping cart
     *
     * @param int $quantity [optional]
     * @param int $variationId [optional]
     * @return bool
     */
    public function canPending($quantity = 1, $variationId = null)
    {
        return $this->_available($quantity, $variationId, true);
    }

    /**
     * @param int $quantity [optional]
     * @param int $variationId [optional]
     * @return bool
     */
    public function canShipping($quantity = 1, $variationId = null)
    {
        return $this->_available($quantity, $variationId, false);
    }

    /**
     *
     * @param int $quantity [optional]
     * @param int $variationId [optional]
     * @return bool
     */
    public function canAddToCart($quantity = 1, $variationId = null)
    {
        return $this->_available($quantity, $variationId, true);
    }

    /**
     * Retrieve product or variation quantity
     *
     * @param int $variationId [optional]
     * @param bool $availableOnly TRUE to get available to buy only [optional]
     * @return mixed (int|bool)
     */
    public function getQuantity($variationId = null, $availableOnly = false)
    {
        if ($variationId) {
            $variation = Axis::single('catalog/product_variation')
                ->find($variationId)
                ->current();
            if (!$variation || $variation->product_id !== $this->product_id) {
                return false;
            }
            $quantity = $variation->quantity;
        } else {
            if (null === $this->_productRow) {
                $this->_productRow = Axis::single('catalog/product')
                    ->find($this->product_id)
                    ->current();
            }
            $quantity = $this->_productRow->quantity;
        }
        if ($availableOnly) {
            $quantity -= $this->min_qty;
        }
        return $this->decimal ? $quantity : floor($quantity);
    }

    /**
     * Update product or variation quantity. Frontend method.
     * Called at Axis_Sales_Model_Order_Status_Run
     *
     * @param float $quantity
     * @param int $variationId [optional]
     * @return boolean
     */
    public function setQuantity($quantity, $variationId = null)
    {
        if (!$this->manage) {
            return true;
        }
        // if backorder quantity change on ship order
        if ($this->backorder && $quantity < $this->min_qty) {
            return true;
        }
        if ($this->decimal) {
            $quantity = floor($quantity);
        }

        $product = Axis::single('catalog/product')
            ->find($this->product_id)
            ->current();

        if ($variationId) {
            $variation = Axis::single('catalog/product_variation')
                ->find($variationId)
                ->current();
            if ($variation->product_id == $this->product_id) {
                $oldQuantity = $variation->quantity;
                $variation->quantity = $quantity;
                $variation->save();
                Axis::dispatch('catalog_product_update_quantity', array(
                    'new_quantity'  => $variation->quantity,
                    'old_quantity'  => $oldQuantity,
                    'variation'     => $variation,
                    'product'       => $product,
                    'stock'         => $this
                ));
                return true;
            }
            return false;
        }

        $oldQuantity = $product->quantity;
        $product->quantity = $quantity;
        $product->save();
        Axis::dispatch('catalog_product_update_quantity', array(
            'new_quantity'  => $product->quantity,
            'old_quantity'  => $oldQuantity,
            'product'       => $product,
            'stock'         => $this
        ));
        return true;
    }

    /**
     * @param Axis_Catalog_Model_Product_Row $row
     * @return Axis_Catalog_Model_Product_Stock_Row
     */
    public function setProductRow(Axis_Catalog_Model_Product_Row $row)
    {
        $this->_productRow = $row;
        return $this;
    }
}