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
 * @package     Axis_Tax
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Tax_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = '';

    public function up()
    {

        Axis::single('admin/menu')
            ->edit('Tax Classes', null, 'tax/class')
            ->edit('Tax Rates', null, 'tax/rate')
        ;

        Axis::single('admin/acl_resource')
            ->rename('admin/tax_class',        'admin/tax/class')
            ->rename('admin/tax_class/index',  'admin/tax/class/index')
            ->rename('admin/tax_class/list',   'admin/tax/class/list')
            ->rename('admin/tax_class/save',   'admin/tax/class/save')
            ->rename('admin/tax_class/delete', 'admin/tax/class/remove')
            ->remove('admin/tax_class')
            ->rename('admin/tax_rate',         'admin/tax/rate')
            ->rename('admin/tax_rate/index',   'admin/tax/rate/index')
            ->rename('admin/tax_rate/list',    'admin/tax/rate/list')
            ->rename('admin/tax_rate/save',    'admin/tax/rate/save')
            ->rename('admin/tax_rate/delete',  'admin/tax/rate/remove')
            ->remove('admin/tax_rate')
        ;
    }

    public function down()
    {

    }
}