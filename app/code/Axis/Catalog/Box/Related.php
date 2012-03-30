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
class Axis_Catalog_Box_Related extends Axis_Catalog_Box_Product_Listing
{
    protected $_title = 'Related Products';
    protected $_class = 'box-related';

    protected $_productsCount = 4;
    protected $_columnsCount  = 4;

    protected function _construct()
    {
        parent::_construct();
        $this->setData('cache_lifetime', 10800); // 3 hours
    }

    protected function _beforeRender()
    {
        $productId = $this->_getCurrentProductId();
        if (!$productId) {
            return false;
        }

        $select = Axis::model('catalog/product')->select('id')
            ->addFilterByAvailability()
            ->joinCategory()
            ->joinInner(
                'catalog_product_related',
                'cpr.related_product_id = cp.id'
            )
            ->where('cpr.product_id = ?', $productId)
            ->where('cc.site_id = ?', Axis::getSiteId())
            ->order(array('cpr.sort_order ASC'))
            ->limit($this->getProductsCount());

        $list = $select->fetchList();

        if (!$list['count']) {
            return false;
        }

        $this->products = $list['data'];
    }

    /**
     * @return int
     */
    protected function _getCurrentProductId()
    {
        $_hurl = Axis_HumanUri::getInstance();
        if ($_hurl->hasParam('product')) {
            $productId = $_hurl->getParamValue('product');
        } else {
            $productId = Zend_Controller_Front::getInstance()
                ->getRequest()
                ->getParam('product', 0);
        }

        return $productId;
    }

    protected function _getCacheKeyParams()
    {
        $keyInfo = parent::_getCacheKeyParams();
        $keyInfo[] = $this->_getCurrentProductId();
        return $keyInfo;
    }
}
