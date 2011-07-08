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
 * @package     Axis_GoogleBase
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_GoogleBase_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        Axis::single('core/config_field')
            ->add('gbase', 'Google Base', null, null, array('translation_module' => 'Axis_GoogleBase'))
            ->add('gbase/main/payment', 'Google Base/General/Payment', 'Discover,American Express,Visa,MasterCard,Check', 'multiple', 'Let your customers buy with all major credit cards', array('config_options' => 'Discover,American Express,Visa,MasterCard,Wire transfer,Check,Cash'))
            ->add('gbase/main/notes', 'Payment notes', 'Google Checkout', 'string')
            ->add('gbase/main/application', 'Application', 'StoreArchitect-Axis-' . Axis::app()->getVersion(), 'string', 'Name of the application that last modified this item.\r\nAll applications should set this attribute whenever they insert or update an item. Recommended format : Organization-ApplicationName-Version')
            ->add('gbase/main/dryRun', 'dryRun', '0', 'bool', "Set 'Yes' for testing, 'No' for production")
            ->add('gbase/main/link', 'Link products to', 'Website', 'select', "If you want to use GoogleBase pages as landing page for your items, or you  can't give the link to your webstore - select Google Base, otherwise - select Website.", array('config_options' => 'GoogleBase,Website'))
            ->add('gbase/main/itemType', 'Item type', 'Products', 'string', 'Type of your products. Read this for more information http://code.google.com/apis/base/starting-out.html#ItemTypes')
            ->add('gbase/auth/login', 'Login', '', 'handler', 'Your google account to submit products', array('model' => 'Crypt'))
            ->add('gbase/auth/password', 'Password', '', 'handler', 'Password to google account', array('model' => 'Crypt'))
            ->add('gbase/auth/connection', 'Connection type', 'AuthSub', 'select', 'Login type. For ClientLogin fill login and password fields. For AuthSub you will have to enter login and password manually', array('config_options' => 'ClientLogin,AuthSub'));

        Axis::single('admin/menu')
            ->add('Marketing', null, 40, 'Axis_Admin')
            ->add('Marketing->Google Base', 'gbase_index', 20, 'Axis_GoogleBase');

        Axis::single('admin/acl_resource')
            ->add('admin/gbase', 'Google Base')
            ->add('admin/gbase_index', 'Google Base')
            ->add("admin/gbase_index/delete")
            ->add("admin/gbase_index/export")
            ->add("admin/gbase_index/export-branch")
            ->add("admin/gbase_index/get-gbase-data")
            ->add("admin/gbase_index/index")
            ->add("admin/gbase_index/revoke-token")
            ->add("admin/gbase_index/set-status")
            ->add("admin/gbase_index/update");
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        Axis::single('core/config_field')->remove('gbase');
        Axis::single('core/config_value')->remove('gbase');

        Axis::single('admin/menu')->remove('Marketing->Google Base');

        Axis::single('admin/acl_resource')
            ->remove('admin/gbase');
    }
}