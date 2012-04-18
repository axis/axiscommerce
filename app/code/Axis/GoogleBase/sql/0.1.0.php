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
        Axis::single('core/config_builder')
            ->section('gbase', 'Google Base')
                ->setTranslation('Axis_GoogleBase')
                ->section('main', 'General')
                    ->option('payment', 'Payment')
                        ->setValue(Axis_GoogleBase_Model_Option_Payment::getDeafult())
                        ->setType('multiple')
                        ->setDescription('Let your customers buy with all major credit cards')
                        ->setModel('googleBase/option_payment')
                    ->option('notes', 'Payment notes', 'Google Checkout')
                    ->option('application', 'Application', 'StoreArchitect-Axis-0.8.5.1 dev')
                        ->setDescription('Name of the application that last modified this item.\r\nAll applications should set this attribute whenever they insert or update an item. Recommended format : Organization-ApplicationName-Version')
                    ->option('dryRun', 'dryRun', false)
                        ->setType('radio')
                        ->setDescription("Set 'Yes' for testing, 'No' for production")
                        ->setModel('core/option_boolean')
                    ->option('link', 'Link products to')
                        ->setValue(Axis_GoogleBase_Model_Option_LinkType::WEBSITE)
                        ->setType('select')
                        ->setDescription("If you want to use GoogleBase pages as landing page for your items, or you  can't give the link to your webstore - select Google Base, otherwise - select Website.")
                        ->setModel('googleBase/option_linkType')
                    ->option('itemType', 'Item type', 'Products')
                        ->setDescription('Type of your products. Read this for more information http://code.google.com/apis/base/starting-out.html#ItemTypes')
                ->section('/main')
                ->section('auth', 'Login')
                    ->option('login', 'Login')
                        ->setDescription('Your google account to submit products')
                        ->setModel('core/option_crypt')
                    ->option('password', 'Password')
                        ->setDescription('Password to google account')
                        ->setModel('core/option_crypt')
                    ->option('connection', 'Connection type')
                        ->setValue(Axis_GoogleBase_Model_Option_ConnectionType::AUTH_SUB)
                        ->setType('select')
                        ->setDescription('Login type. For ClientLogin fill login and password fields. For AuthSub you will have to enter login and password manually')
                        ->setModel('googleBase/option_connectionType')

            ->section('/')
            ;
    }

    public function down()
    {
        Axis::single('core/config_builder')->remove('gbase');
    }
}