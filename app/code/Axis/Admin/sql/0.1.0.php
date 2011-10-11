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
 * @package     Axis_Admin
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Admin_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        Axis::single('admin/acl_resource')
            ->add('admin', 'All')
            ->add('admin/index', 'Home')
            ->add("admin/index/ajax-content")
            ->add("admin/index/change-site")
            ->add("admin/index/dash-board-chart")
            ->add("admin/index/index")
            ->add("admin/index/info")

            ->add('admin/template', 'Design Control')

            ->add("admin/template_box", "Boxes")
            ->add("admin/template_box/batch-save")
            ->add("admin/template_box/delete")
            ->add("admin/template_box/edit")
            ->add("admin/template_box/index")
            ->add("admin/template_box/list")
            ->add("admin/template_box/save")

            ->add('admin/template_index', 'Templates')
            ->add("admin/template_index/delete")
            ->add("admin/template_index/export")
            ->add("admin/template_index/get-info")
            ->add("admin/template_index/get-nodes")
            ->add("admin/template_index/list-xml-templates")
            ->add("admin/template_index/import")
            ->add("admin/template_index/index")
            ->add("admin/template_index/save")

            ->add("admin/template_layout", 'Layouts')
            ->add("admin/template_layout/delete")
            ->add("admin/template_layout/list")
            ->add("admin/template_layout/list-collect")
            ->add("admin/template_layout/save")


            ->add('admin/template_mail', 'Emails Templates')
            ->add("admin/template_mail/delete")
            ->add("admin/template_mail/get-info")
            ->add("admin/template_mail/index")
            ->add("admin/template_mail/list")
            ->add("admin/template_mail/list-event")
            ->add("admin/template_mail/list-mail")
            ->add("admin/template_mail/list-template")
            ->add("admin/template_mail/save")

            ->add('admin/pages', 'Pages')
            ->add("admin/pages/delete")
            ->add("admin/pages/index")
            ->add("admin/pages/list")
            ->add("admin/pages/save")

            ->add('admin/configuration', 'Configuration')
            ->add("admin/configuration/copy-global")
            ->add("admin/configuration/edit")
            ->add("admin/configuration/get-field-models")
            ->add("admin/configuration/get-field-types")
            ->add("admin/configuration/get-nodes")
            ->add("admin/configuration/index")
            ->add("admin/configuration/list")
            ->add("admin/configuration/save")
            ->add("admin/configuration/save-field")
            ->add("admin/configuration/use-global")

            ->add('admin/site', 'Sites')
            ->add("admin/site/delete")
            ->add("admin/site/get-list")
            ->add("admin/site/index")
            ->add("admin/site/save")

            ->add('admin/users', 'Admin Users')
            ->add("admin/users/delete")
            ->add("admin/users/get-list")
            ->add("admin/users/get-roles")
            ->add("admin/users/index")
            ->add("admin/users/save")

            ->add('admin/roles', 'Roles')
            ->add("admin/roles/add")
            ->add("admin/roles/delete")
            ->add("admin/roles/edit")
            ->add("admin/roles/get-nodes")
            ->add("admin/roles/get-parent-allows")
            ->add("admin/roles/index")
            ->add("admin/roles/save")

            ->add('admin/cache', 'Cache Management')
            ->add("admin/cache/clean")
            ->add("admin/cache/clean-all")
            ->add("admin/cache/get-list")
            ->add("admin/cache/index")
            ->add("admin/cache/save")

            ->add('admin/module', 'Modules')
            ->add("admin/module/get-list")
            ->add("admin/module/index")
            ->add("admin/module/install")
            ->add("admin/module/uninstall")
            ->add("admin/module/upgrade");
    }

    public function down()
    {

    }
}