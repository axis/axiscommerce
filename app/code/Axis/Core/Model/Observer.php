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
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Observer
{
    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'design' => array(
                'label'         => 'Design Control',
                'order'         => 100,
                'translator'    => 'Axis_Admin',
                'uri'           => '#',
                'pages'         => array(
                    'core/theme' => array(
                        'label'         => 'Themes',
                        'order'         => 10,
                        'module'        => 'Axis_Core',
                        'controller'    => 'theme',
                        'action'        => 'index',
                        'route'         => 'admin/axis/core',
                        'resource'      => 'admin/axis/core/theme/index'
                    ),
                    'core/mail' => array(
                        'label'         => 'Email Templates',
                        'order'         => 20,
                        'module'        => 'Axis_Core',
                        'controller'    => 'mail',
                        'action'        => 'index',
                        'route'         => 'admin/axis/core',
                        'resource'      => 'admin/axis/core/mail/index'
                    ),
                    'core/page' => array(
                        'label'         => 'Pages',
                        'order'         => 30,
                        'module'        => 'Axis_Core',
                        'controller'    => 'page',
                        'action'        => 'index',
                        'route'         => 'admin/axis/core',
                        'resource'      => 'admin/axis/core/page/index'
                    )
                )
            ),
            'admin' => array(
                'pages' => array(
                    'core/config_value' => array(
                        'label'         => 'Configuration',
                        'order'         => 10,
                        'module'        => 'Axis_Core',
                        'controller'    => 'config_value',
                        'action'        => 'index',
                        'route'         => 'admin/axis/core',
                        'resource'      => 'admin/axis/core/config_value/index'
                    ),
                    'core/site' => array(
                        'label'         => 'Site',
                        'order'         => 20,
                        'module'        => 'Axis_Core',
                        'controller'    => 'site',
                        'action'        => 'index',
                        'route'         => 'admin/axis/core',
                        'resource'      => 'admin/axis/core/site/index'
                    ),
                    'core/cache' => array(
                        'label'         => 'Cache Management',
                        'order'         => 50,
                        'module'        => 'Axis_Core',
                        'controller'    => 'cache',
                        'action'        => 'index',
                        'route'         => 'admin/axis/core',
                        'resource'      => 'admin/axis/core/cache/index'
                    ),
                    'core/module' => array(
                        'label'         => 'Modules',
                        'order'         => 60,
                        'module'        => 'Axis_Core',
                        'controller'    => 'module',
                        'action'        => 'index',
                        'route'         => 'admin/axis/core',
                        'resource'      => 'admin/axis/core/module/index'
                    )
                )
            )
        ));
    }

    public function flushConfigOptionCache(Axis_Db_Table_Row $row)
    {
        list($path) = explode('/', $row->path);
        if (0 == $row->site_id) {
            $sites = array_keys(Axis::model('core/option_site')->toArray());
        } else {
            $sites = array($row->site_id);
        }
        $cache = Axis::cache();
        foreach ($sites as $siteId) {
            $cacheId = "config_{$path}_site_{$siteId}";
            $cache->remove($cacheId);
        }
    }
}
