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
 * @abstract
 */
abstract class Axis_Catalog_Box_Product_Abstract extends Axis_Core_Box_Abstract
{
    protected function _construct()
    {
        $this->setData('cache_tags', 'catalog_product');
    }

    protected function _getProductId()
    {
        $productId = $this->product_id; // received from box configuration

        if (!is_numeric($productId)) {
            $_hurl = Axis_HumanUri::getInstance();
            if ($_hurl->hasParam('product')) {
                $productId = $_hurl->getParamValue('product');
            }
        }
        if (!is_numeric($productId)) {
            $productId = Zend_Controller_Front::getInstance()
                ->getRequest()
                ->getParam('product', 0);
        }

        if (Zend_Registry::isRegistered('catalog/current_product')) {
            $product = Zend_Registry::get('catalog/current_product');
            if (!$productId || $product->id == $productId) {
                $this->product = $product;
            }
        }

        return $productId;
    }

    public function getConfigurationFields()
    {
        return array(
            'product_id' => array(
                'fieldLabel'    => Axis::translate('catalog')->__('Product Id'),
                'xtype'         => 'numberfield'
            )
        );
    }

    protected function _getCacheKeyInfo()
    {
        return array(
            $this->_getProductId(),
            Axis::single('locale/currency')->getCode(),
            Axis::model('account/customer')->getGroupId()
        );
    }
}
