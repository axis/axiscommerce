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
 * @package     Axis_Core
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Core_Upgrade_0_2_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.4';
    protected $_info = '';

    public function up()
    {
        Axis::model('admin/acl_rule')
            ->rename('admin/cache',          'admin/core/cache')
            ->rename('admin/cache/index',    'admin/core/cache/index')
            ->rename('admin/cache/get-list', 'admin/core/cache/list')
            ->rename('admin/cache/save',     'admin/core/cache/batch-save')
            ->rename('admin/cache/clean',    'admin/core/cache/remove')

            ->rename('admin/configuration',                  'admin/core/config-field')
            ->rename('admin/configuration/get-nodes',        'admin/core/config-field/list')
            ->rename('admin/configuration/save-field',       'admin/core/config-field/save')
            ->rename('admin/configuration/get-field-types',  'admin/core/config-field/list-type')
            ->rename('admin/configuration/get-field-models', 'admin/core/config-field/list-model')

            ->rename('admin/configuration/list',        'admin/core/config-value/list')
            ->rename('admin/configuration/edit',        'admin/core/config-value/load')
            ->rename('admin/configuration/save',        'admin/core/config-value/save')
            ->rename('admin/configuration/use-global',  'admin/core/config-value/use-global')
            ->rename('admin/configuration/copy-global', 'admin/core/config-value/copy-global')

            ->rename('admin/module',           'admin/core/module')
            ->rename('admin/module/get-list',  'admin/core/module/list')
            ->rename('admin/module/install',   'admin/core/module/install')
            ->rename('admin/module/uninstall', 'admin/core/module/uninstall')
            ->rename('admin/module/upgrade/',  'admin/core/module/upgrade')

            ->rename('admin/pages',        'admin/core/page')
            ->rename('admin/pages/index',  'admin/core/page/index')
            ->rename('admin/pages/list',   'admin/core/page/list')
            ->rename('admin/pages/save',   'admin/core/page/batch-save')
            ->rename('admin/pages/delete', 'admin/core/page/remove')

            ->rename('admin/site',          'admin/core/site')
            ->rename('admin/site/index',    'admin/core/site/index')
            ->rename('admin/site/get-list', 'admin/core/site/list')
            ->rename('admin/site/save',     'admin/core/site/batch-save')
            ->rename('admin/site/delete',   'admin/core/site/remove')

            ->rename('admin/template_index',           'admin/core/theme')
            ->rename('admin/template_index/index',     'admin/core/theme/index')
            ->rename('admin/template_index/get-nodes', 'admin/core/theme/list')
            ->rename('admin/template_index/load',      'admin/core/theme/load')
            ->rename('admin/template_index/save',      'admin/core/theme/save')
            ->rename('admin/template_index/delete',    'admin/core/theme/remove')
            ->rename('admin/template_index/export',    'admin/core/theme/export')
            ->rename('admin/template_index/import',    'admin/core/theme/import')

            ->rename('admin/template_box', 'admin/core/theme_block')
//            ->rename('admin/template_box/index')
            ->rename('admin/template_box/list',       'admin/core/theme_block/list')
            ->rename('admin/template_box/edit',       'admin/core/theme_block/load')
            ->rename('admin/template_box/save',       'admin/core/theme_block/save')
            ->rename('admin/template_box/batch-save', 'admin/core/theme_block/batch-save')
            ->rename('admin/template_box/delete',     'admin/core/theme_block/remove')

            ->rename('admin/template_page',        'admin/core/theme_page')
            ->rename('admin/template_page/list',   'admin/core/theme_page/list')
            ->rename('admin/template_page/save',   'admin/core/theme_page/batch-save')
            ->rename('admin/template_page/delete', 'admin/core/theme_page/remove')

            ->rename('admin/template_layout',      'admin/core/theme_layout')
            ->rename('admin/template_layout/list', 'admin/core/theme_layout/list')

            ->rename('admin/template_mail',               'admin/core/mail')
            ->rename('admin/template_mail/index',         'admin/core/mail/index')
            ->rename('admin/template_mail/list',          'admin/core/mail/list')
            ->rename('admin/template_mail/get-info',      'admin/core/mail/load')
            ->rename('admin/template_mail/list-event',    'admin/core/mail/list-event')
            ->rename('admin/template_mail/list-template', 'admin/core/mail/list-template')
            ->rename('admin/template_mail/list-mail',     'admin/core/mail/list-mail')
            ->rename('admin/template_mail/save',          'admin/core/mail/batch-save')
            ->rename('admin/template_mail/delete',        'admin/core/mail/remove')

            ;
    }
}