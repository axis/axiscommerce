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
 * @package     Axis_Community
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Community_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_resource')
            ->rename('admin/community_rating',          'admin/community/rating')
            ->rename('admin/community_rating/index',    'admin/community/rating/index')
            ->rename('admin/community_rating/get-list', 'admin/community/rating/list')
            ->rename('admin/community_rating/save',     'admin/community/rating/batch-save')
            ->rename('admin/community_rating/delete',   'admin/community/rating/remove')

            ->rename('admin/community_review',                   'admin/community/review')
            ->rename('admin/community_review/index',             'admin/community/review/index')
            ->rename('admin/community_review/get-list',          'admin/community/list')
            ->rename('admin/community_review/save',              'admin/community/review/save')
            ->rename('admin/community_review/delete',            'admin/community/review/remove')
            ->rename('admin/community_review/get-product-list',  'admin/community/review/get-product-list')
            ->rename('admin/community_review/get-customer-list', 'admin/community/review/get-customer-list')
            ->remove('admin/community_')
        ;
    }

    public function down()
    {
    }
}