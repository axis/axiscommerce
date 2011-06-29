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
 * @copyright   Copyright 2008-2011 Axis
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
        $select = Axis::model('log/event')->select('object_id')
            ->distinct()
            ->limit($this->getProductsCount())
            ->order('le.id DESC')
            ;
        $customerId = $visitor->customer_id;
        if ($customerId && $customerId === Axis::getCustomerId()) {
            $select->join('log_visitor', 'le.visitor_id = lv.id')
                ->where('lv.customer_id = ?', $customerId);    
        } else {
            $select->where('visitor_id = ?', $visitor->id);
        }
        $productIds = $select->fetchCol();
        
        if (empty ($productIds)) {
            return false;
        }
        
        $products = Axis::model('catalog/product')->select('*')
            ->addFilterByAvailability()
            ->addCommonFields()
            ->addFinalPrice()
            ->joinCategory()
            ->where('cc.site_id = ?', Axis::getSiteId())
            ->where('cp.id IN (?)', $productIds)
            ->fetchProducts($productIds)
            ;
        
        if (empty($products)) {
            return false;
        }

        $this->products = $products;
        return $this->hasProducts();
    }
}