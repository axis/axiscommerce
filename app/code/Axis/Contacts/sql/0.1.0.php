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
 * @package     Axis_Contacts
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Contacts_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('contacts_department')}`;
        CREATE TABLE  `{$installer->getTable('contacts_department')}` (
          `id` smallint(5) unsigned NOT NULL auto_increment,
          `name` varchar(128) NOT NULL,
          `email` varchar(128) NOT NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

        INSERT INTO `{$installer->getTable('contacts_department')}` (`id`, `name`, `email`) VALUES (1, 'Support', 'test@axiscommerce.com'),(2, 'General', 'test@axiscommerce.com'),(3, 'Shopping', 'test@axiscommerce.com'),(4, 'Developers', 'test@axiscommerce.com');

        -- DROP TABLE IF EXISTS `{$installer->getTable('contacts_message')}`;
        CREATE TABLE  `{$installer->getTable('contacts_message')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `email` varchar(64) NOT NULL,
          `subject` varchar(128) NOT NULL,
          `message` text,
          `custom_info` text,
          `department_id` smallint(5) unsigned default NULL,
          `created_at` datetime NOT NULL,
          `message_status` enum('new','read','replied') NOT NULL default 'new',
          `site_id` smallint(5) NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `FK_contacts_message_department_id` (`department_id`),
          CONSTRAINT `FK_contacts_message_department_id` FOREIGN KEY (`department_id`) REFERENCES `{$installer->getTable('contacts_department')}` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        ");

        Axis::single('admin/menu')
            ->add('Customers', null, 60, 'Axis_Account')
            ->add('Customers->Contact Us', 'contacts_index', 50, 'Axis_Contacts');

        Axis::single('admin/acl_resource')
            ->add('admin/contacts', 'Contacts')
            ->add('admin/contacts_index', 'Contact Us')
            ->add("admin/contacts_index/delete")
            ->add("admin/contacts_index/delete-department")
            ->add("admin/contacts_index/get-department")
            ->add("admin/contacts_index/get-departments")
            ->add("admin/contacts_index/index")
            ->add("admin/contacts_index/list")
            ->add("admin/contacts_index/save-department")
            ->add("admin/contacts_index/send")
            ->add("admin/contacts_index/set-status");

        Axis::single('core/page')
            ->add('contacts/*/*');
    }

    public function down()
    {

    }
}