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
 * @subpackage  Axis_Discount_Box
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Discount_Box_Special extends Axis_Catalog_Box_Product_Listing
{
    protected $_title = 'Special Products';
    protected $_class = 'box-special';

    protected function _beforeRender()
    {
        $ids = Axis::single('discount/discount')->cache()->getSpecialProducts(
            Axis_HumanUri::getInstance()->getParamValue('cat'),
            $this->getProductsCount()
        );

        if (!count($ids)) {
            return false;
        }

        $products = Axis::single('catalog/product')->select('*')
            ->addCommonFields()
            ->addFinalPrice()
            ->where('cp.id IN (?)', $ids)
            ->fetchProducts($ids);

        $this->products = $products;
        return $this->hasProducts();
    }
}