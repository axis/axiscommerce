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
 * @package     Axis_Cms
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Cms_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = '';

    public function up()
    {
        Axis::model('admin/menu')
            ->edit('Categories/Pages', null, 'cms/page')
        ;
        
        Axis::model('admin/acl_resource')
//            ->add('admin/cms', 'CMS')
//            ->add('admin/cms_index', 'Categories/Pages')
            ->add('admin/cms/page', 'Pages')
            ->rename("admin/cms_index/delete-category", "admin/cms/page/delete-category")
            ->rename("admin/cms_index/delete-page", "admin/cms/page/delete-page")
            ->rename("admin/cms_index/get-category", "admin/cms/page/get-category-data")
            ->rename("admin/cms_index/get-page-data", "admin/cms/page/get-page-data")
            ->rename("admin/cms_index/get-pages", "admin/cms/page/get-pages")
            ->rename("admin/cms_index/get-site-tree", "admin/cms/page/get-site-tree")
            ->rename("admin/cms_index/index", "admin/cms/page/index")
            ->rename("admin/cms_index/move-category", "admin/cms/page/move-category")
            ->rename("admin/cms_index/quick-save-page", "admin/cms/page/quick-save-page")
            ->rename("admin/cms_index/save-category", "admin/cms/page/save-category")
            ->rename("admin/cms_index/save-page", "admin/cms/page/save-page")
            
        ;
    }

    public function down()
    {

    }
}