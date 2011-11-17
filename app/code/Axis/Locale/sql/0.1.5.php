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
 * @package     Axis_Locale
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Locale_Upgrade_0_1_5 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.5';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_rule')
            ->rename('admin/locale_currency',            'admin/locale/currency')
            ->rename('admin/locale_currency/index',      'admin/locale/currency/index')
            ->rename('admin/locale_currency/list',       'admin/locale/currency/list')
            ->rename('admin/locale_currency/save',       'admin/locale/currency/save')
            ->rename('admin/locale_currency/batch-save', 'admin/locale/currency/batch-save')
            ->rename('admin/locale_currency/delete',     'admin/locale/currency/remove')

            ->rename('admin/locale_language',        'admin/locale/language')
            ->rename('admin/locale_language/index',  'admin/locale/language/index')
            ->rename('admin/locale_language/list',   'admin/locale/language/list')
            ->rename('admin/locale_language/save',   'admin/locale/language/save')
            ->rename('admin/locale_language/delete', 'admin/locale/language/remove')
            ->rename('admin/locale_language/change', 'admin/locale/language/change')
        ;

    }
}