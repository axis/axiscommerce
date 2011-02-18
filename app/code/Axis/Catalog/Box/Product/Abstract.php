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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Catalog_Box_Product_Abstract extends Axis_Core_Box_Abstract
{
    protected function _getProductId()
    {
        $productId = $this->productId;
        if (is_numeric($productId)){
            return $productId;
        }
        $_hurl = Axis_HumanUri::getInstance();
        if ($_hurl->hasParam('product') && ($productId = $_hurl->getParamValue('product'))) {
            return $productId;
        }
        return Zend_Controller_Front::getInstance()->getRequest()->getParam('product', 0);
    }

    public function init()
    {
        $this->productId = $this->_getProductId();

        if (Zend_Registry::isRegistered('catalog/current_product')) {
            $product = Zend_Registry::get('catalog/current_product');
            if (!$this->productId || $product->id == $this->productId) {
                $this->product = $product;
            }
        }
    }
}
