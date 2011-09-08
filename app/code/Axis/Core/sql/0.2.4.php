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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Core_Upgrade_0_2_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.4';
    protected $_info = '';

    public function up()
    {
        Axis::model('admin/menu')
            ->edit('Cache Management', null, 'core/cache')
            ->edit('Configuration', null, 'core/config-value')
            ->edit('Modules', null, 'core/module')
//            ->add('Design Control', null, 100, 'Axis_Admin')
//            ->add('Design Control->Templates', 'template_index', 10)
//            ->add('Design Control->Email Templates', 'template_mail', 20)
//            ->add('Design Control->Pages', 'pages', 30)
        ;
        
        Axis::model('admin/acl_resource')
            ->add('admin/core')
            ->rename('admin/cache', 'admin/core/cache')
            ->rename('admin/cache/index', 'admin/core/cache/index')
            ->rename('admin/cache/get-list', 'admin/core/cache/list')
            ->rename('admin/cache/save', 'admin/core/cache/batch-save')
            ->rename('admin/cache/clean', 'admin/core/cache/remove')
            ->remove('admin/cache/clean-all')
            ->remove('admin/cache')
            
            ->rename('admin/configuration', 'admin/core/config-field')
            ->rename('admin/configuration/get-nodes', 'admin/core/config-field/list')
            ->rename('admin/configuration/save-field', 'admin/core/config-field/save')
            ->rename('admin/configuration/get-field-types', 'admin/core/config-field/list-type')
            ->rename('admin/configuration/get-field-models', 'admin/core/config-field/list-model')
            
            ->add('admin/core/config-value')
            ->rename('admin/configuration/list', 'admin/core/config-value/list')
            ->rename('admin/configuration/edit', 'admin/core/config-value/load')
            ->rename('admin/configuration/save', 'admin/core/config-value/save')
            ->rename('admin/configuration/use-global', 'admin/core/config-value/use-global')
            ->rename('admin/configuration/copy-global', 'admin/core/config-value/copy-global')
            ->remove('admin/configuration/')
            
            ->rename('admin/module', 'admin/core/module')
            ->rename('admin/module/get-list', 'admin/core/module/list')
            ->rename('admin/module/install', 'admin/core/module/install')
            ->rename('admin/module/uninstall', 'admin/core/module/uninstall')
            ->rename('admin/module/upgrade/', 'admin/core/module/upgrade')
            ->remove('admin/module/')
            
            ->rename('admin/pages', 'admin/core/page')
            ->rename('admin/pages/index', 'admin/core/page/index')
            ->rename('admin/pages/list', 'admin/core/page/list')
            ->rename('admin/pages/save', 'admin/core/page/batch-save')
            ->rename('admin/pages/delete', 'admin/core/page/remove')
            ->rename('admin/pages')
            
            ->rename('admin/site', 'admin/core/site')
            ->rename('admin/site/index', 'admin/core/site/index')
            ->rename('admin/site/get-list', 'admin/core/site/list')
            ->rename('admin/site/save', 'admin/core/site/batch-save')
            ->rename('admin/site/delete', 'admin/core/site/remove')
            ->remove('admin/site')
            ;
    }

    public function down()
    {

    }
}