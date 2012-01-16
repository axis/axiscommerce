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
 * @subpackage  Axis_Catalog_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_ProductCompareController extends Axis_Core_Controller_Front
{
    public function indexAction()
    {
        $this->setTitle(
            Axis::translate('catalog')->__(
                'Product Comparison'
        ));

        if (!Axis::single('catalog/product_compare')->hasItems()) {
            $this->render('empty');
            return;
        }

        $productIds = Axis::single('catalog/product_compare')->getItems();

        $products = Axis::single('catalog/product')
            ->select('*')
            ->addCommonFields()
            ->addFinalPrice()
            ->where('cp.id IN (?)', $productIds)
            ->fetchProducts($productIds);

        $comparableAttributes = Axis::single('catalog/product_attribute')
            ->getComparable($productIds);

        $options = array();
        foreach ($comparableAttributes as $productId => $productOptions) {
            $products[$productId]['options'] = $productOptions;
            foreach ($productOptions as $optionId => $option) {
                $options[$optionId] = $option['name'];
            }
        }
        $this->view->products = $products;
        $this->view->options = array_unique($options);
        $this->render();
    }

    public function addAction()
    {
        $productId = $this->_getParam('product', 0);
        if (!$productId) {
            return;
        }
        $productTable = Axis::single('catalog/product');
        if (!count($productTable->find($productId))) {
            return;
        }
        Axis::single('catalog/product_compare')->add($productId);
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }

    public function removeAction()
    {
        $productId = $this->_getParam('product', 0);
        if (!$productId) {
            return;
        }
        Axis::single('catalog/product_compare')->remove($productId);
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }

    public function compareAction()
    {
        if (!$this->_hasParam('product_id')) {
            return $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
        }
        $mCompare = Axis::model('catalog/product_compare');
        $mCompare->clear();
        foreach ($this->_getParam('product_id') as $productId) {
            $mCompare->add($productId);
        }
        $this->_redirect($this->view->catalogUrl . '/product-compare');
    }
}