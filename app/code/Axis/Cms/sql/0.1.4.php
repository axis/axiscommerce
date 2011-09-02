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
            ->edit('Static Blocks', null, 'cms/block')
        ;
        
        Axis::model('admin/acl_resource')
//            ->add('admin/cms', 'CMS')
            ->add('admin/cms/page', 'Pages')
            ->rename('admin/cms_index/index',           'admin/cms/page/index')
            ->rename('admin/cms_index/get-pages',       'admin/cms/page/list')
            ->rename('admin/cms_index/get-page-data',   'admin/cms/page/load')
            ->rename('admin/cms_index/save-page',       'admin/cms/page/save')
            ->rename('admin/cms_index/quick-save-page', 'admin/cms/page/batch-save')
            ->rename('admin/cms_index/delete-page',     'admin/cms/page/remove')
            ->add('admin/cms/category', 'Categories')
            ->rename('admin/cms_index/get-site-tree',   'admin/cms/category/list')
            ->rename('admin/cms_index/get-category',    'admin/cms/category/load')
            ->rename('admin/cms_index/save-category',   'admin/cms/category/save')
            ->rename('admin/cms_index/delete-category', 'admin/cms/category/remove')
            ->rename('admin/cms_index/move-category',   'admin/cms/category/move')
            ->remove('admin/cms_index')
            
            ->add('admin/cms/block', 'Static Blocks')
            ->rename('admin/cms_block/index',            'admin/cms/block/index')
            ->rename('admin/cms_block/get-blocks',       'admin/cms/block/list')
            ->rename('admin/cms_block/get-block-data',   'admin/cms/block/load')
            ->rename('admin/cms_block/save-block',       'admin/cms/block/save')
            ->rename('admin/cms_block/quick-save-block', 'admin/cms/block/batch-save')
            ->rename('admin/cms_block/delete-block',     'admin/cms/block/remove')
            ->remove('admin/cms_block')
            
            ->add('admin/cms/comment', 'Page Comments')
            ->rename('admin/cms_comment/index',          'admin/cms/comment/index')
            ->rename('admin/cms_comment/get-comments',   'admin/cms/comment/list')
            ->rename('admin/cms_comment/save-comment',   'admin/cms/comment/save')
            ->rename('admin/cms_comment/quick-save',     'admin/cms/comment/batch-save')
            ->rename('admin/cms_comment/delete-comment', 'admin/cms/comment/remove')
            ->rename('admin/cms_comment/get-page-tree',  'admin/cms/comment/get-page-tree')
            
            ->remove('admin/cms_comment')
        ;
    }

    public function down()
    {

    }
}