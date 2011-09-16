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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Account_Upgrade_0_2_6 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.6';
    protected $_info = 'ACL resources was modified';

    public function up()
    {
        Axis::model('admin/acl_resource')
            ->remove('admin/account/field/list-type')
            ->remove('admin/account/field/list-validator')
            ->rename('admin/account/value-set/save', 'admin/account/value-set/batch-save')
            ->rename('admin/account/value-set-value/save', 'admin/account/value-set-value/batch-save');
    }

    public function down()
    {
    }
}
