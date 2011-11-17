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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Catalog_Upgrade_0_2_7 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.7';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_rule')
            ->rename('admin/catalog_index',                              'admin/catalog/product')
            ->rename('admin/catalog_index/index',                        'admin/catalog/product/index')
            ->rename('admin/catalog_index/list-products',                'admin/catalog/product/list')
            ->rename('admin/catalog_index/list-bestseller',              'admin/catalog/product/list-bestseller')
            ->rename('admin/catalog_index/list-viewed',                  'admin/catalog/product/list-viewed')
            ->rename('admin/catalog_index/get-product-info',             'admin/catalog/product/load')
            ->rename('admin/catalog_index/save-product',                 'admin/catalog/product/save')
            ->rename('admin/catalog_index/batch-save-product',           'admin/catalog/product/batch-save')
            ->rename('admin/catalog_index/save-image',                   'admin/catalog/product/save-image')
            ->rename('admin/catalog_index/remove-product',               'admin/catalog/product/remove')
            ->rename('admin/catalog_index/remove-product-from-category', 'admin/catalog/product/remove-product-from-category')
            ->rename('admin/catalog_index/remove-product-from-site',     'admin/catalog/product/remove-product-from-site')
            ->rename('admin/catalog_index/move-products',                'admin/catalog/product/batch-move')
            ->rename('admin/catalog_index/update-search-index',          'admin/catalog/product/update-search-index')

            ->rename('admin/catalog_product-attributes',        'admin/catalog/product-option')
            ->rename('admin/catalog_index/get-options',         'admin/catalog/product-option/list')
            ->rename('admin/catalog_product-attributes/index',  'admin/catalog/product-option/index')
            ->rename('admin/catalog_product-attributes/list',   'admin/catalog/product-option/list')
            ->rename('admin/catalog_product-attributes/load',   'admin/catalog/product-option/load')
            ->rename('admin/catalog_product-attributes/save',   'admin/catalog/product-option/save')
            ->rename('admin/catalog_product-attributes/delete', 'admin/catalog/product-option/remove')

            ->rename('admin/catalog_product-option-valueset',             'admin/catalog/product-option-valueset')
            ->rename('admin/catalog_product-option-valueset/index',       'admin/catalog/product-option-valueset/index')
            ->rename('admin/catalog_product-option-valueset/list-sets',   'admin/catalog/product-option-valueset/list')
            ->rename('admin/catalog_product-option-valueset/save-set',    'admin/catalog/product-option-valueset/save')
            ->rename('admin/catalog_product-option-valueset/delete-sets', 'admin/catalog/product-option-valueset/remove')

            ->rename('admin/catalog_product-option-valueset/list-values',   'admin/catalog/product-option-value/list')
            ->rename('admin/catalog_product-option-valueset/save-values',   'admin/catalog/product-option-value/batch-save')
            ->rename('admin/catalog_product-option-valueset/delete-values', 'admin/catalog/product-option-value/remove')

            ->rename('admin/catalog_manufacturer',            'admin/catalog/manufacturer')
            ->rename('admin/catalog_manufacturer/index',      'admin/catalog/manufacturer/index')
            ->rename('admin/catalog_manufacturer/list',       'admin/catalog/manufacturer/list')
            ->rename('admin/catalog_manufacturer/save',       'admin/catalog/manufacturer/save')
            ->rename('admin/catalog_manufacturer/delete',     'admin/catalog/manufacturer/remove')
            ->rename('admin/catalog_manufacturer/save-image', 'admin/catalog/manufacturer/save-image')

            ->rename('admin/catalog_image',           'admin/catalog/image')
            ->rename('admin/catalog_image/tre-panel', 'admin/catalog/image/cmd')

            ->rename('admin/catalog_category',           'admin/catalog/category')
            ->rename('admin/catalog_category/get-items', 'admin/catalog/category/list')
            ->rename('admin/catalog_category/get-data',  'admin/catalog/category/load')
            ->rename('admin/catalog_category/save',      'admin/catalog/category/save')
            ->rename('admin/catalog_category/delete',    'admin/catalog/category/remove')
            ->rename('admin/catalog_category/move',      'admin/catalog/category/move')
        ;
    }
}