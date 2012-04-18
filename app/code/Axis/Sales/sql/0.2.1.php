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
 * @package     Axis_Sales
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Sales_Upgrade_0_2_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.1';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_rule')
            ->rename('admin/sales_order',                'admin/sales/order')
            ->rename('admin/sales_order/index',          'admin/sales/order/index')
            ->rename('admin/sales_order/list',           'admin/sales/order/list')
            ->rename('admin/sales_order/get-order-info', 'admin/sales/order/load')
            ->rename('admin/sales_order/set-status',     'admin/sales/order/save')
            ->rename('admin/sales_order/delete',         'admin/sales/order/remove')
            ->rename('admin/sales_order/print',          'admin/sales/order/print')
//            ->rename('admin/sales_order/get-product-attribute-form')
            ->rename('admin/sales_order/add-product-to-order', 'admin/sales/order/add-product')

            ->rename('admin/sales_order-status',            'admin/sales/order-status')
            ->rename('admin/sales_order-status/index',      'admin/sales/order-status/index')
            ->rename('admin/sales_order-status/list',       'admin/sales/order-status/list')
            ->rename('admin/sales_order-status/get-info',   'admin/sales/order-status/load')
            ->rename('admin/sales_order-status/save',       'admin/sales/order-status/save')
            ->rename('admin/sales_order-status/batch-save', 'admin/sales/order-status/batch-save')
            ->rename('admin/sales_order-status/delete',     'admin/sales/order-status/remove')
            ->rename('admin/sales_order-status/get-childs', 'admin/sales/order-status/get-childs')
            
        ;
    }
}