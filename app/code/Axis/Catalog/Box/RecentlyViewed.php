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
class Axis_Catalog_Box_RecentlyViewed extends Axis_Catalog_Box_Product_Listing
{
    protected $_title = 'Recently Viewed';
    protected $_class = 'box-recently-viewed';

    protected function _beforeRender()
    {
        $visitor = Axis::model('log/visitor')->getVisitor();
        // select all viewed products by current visitor
        $selectInner = Axis::model('log/event')
            ->select(array('id', 'object_id'))
            ->order('le.id DESC');

        $customerId = $visitor->customer_id;
        if ($customerId && $customerId === Axis::getCustomerId()) {
            $selectInner->join('log_visitor', 'le.visitor_id = lv.id')
                ->where('lv.customer_id = ?', $customerId);
        } else {
            $selectInner->where('visitor_id = ?', $visitor->id);
        }

        // filter unique product_ids from correctly ordered query
        // using adapter for specific from statement
        // this subquery is used to get the correct order for products
        // bigfix for not displayed product if the user viewed it some time ago and now opened it again
        // with single query this product will not outputted first in a row
        $adapter = Axis::model('log/event')->getAdapter();
        $select = $adapter->select()
            ->from(array('le' => $selectInner), 'object_id')
            ->group('le.object_id')
            ->order('le.id DESC')
            ->limit($this->getProductsCount());

        $productIds = $adapter->fetchCol($select);
        if (empty($productIds)) {
            return false;
        }

        $products = Axis::model('catalog/product')->select('*')
            ->addFilterByAvailability()
            ->addCommonFields()
            ->addFinalPrice()
            ->joinCategory()
            ->where('cc.site_id = ?', Axis::getSiteId())
            ->where('cp.id IN (?)', $productIds)
            ->fetchProducts($productIds);

        if (empty($products)) {
            return false;
        }

        $this->products = $products;
        return $this->hasProducts();
    }
}
