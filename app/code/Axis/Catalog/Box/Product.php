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
 * @subpackage  Axis_Catalog_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Box_Product extends Axis_Catalog_Box_Product_Abstract
{
    protected $_title = 'Description';
    protected $_class = 'box-product-info';

    protected function _beforeRender()
    {
        if (!$this->product_id = $this->_getProductId()) {
            return false;
        }

        if ($this->product instanceof Zend_Db_Table_Row
            && $this->product->id == $this->product_id) {

            return true;
        }

        $product = Axis::single('catalog/product')->find($this->product_id)
            ->current();
        if (!$product) {
            return false;
        }

        $this->product = $product;
    }
}
