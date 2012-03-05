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
 * @package     Axis_Tag
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Tag_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('tag_customer')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('tag_customer')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `customer_id` int(10) unsigned default NULL,
            `site_id` smallint(5) unsigned NOT NULL,
            `name` varchar(128) NOT NULL,
            `status` TINYINT(1) NOT NULL default '1',
            PRIMARY KEY  (`id`),
            KEY `i_site_id` USING BTREE (`site_id`),
            KEY `i_customer_id` USING BTREE (`customer_id`),
            CONSTRAINT `FK_customer_tag_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE SET NULL,
            CONSTRAINT `FK_customer_tag_site` FOREIGN KEY (`site_id`) REFERENCES `{$installer->getTable('core_site')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('tag_product')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('tag_product')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `customer_tag_id` int(10) unsigned NOT NULL,
            `product_id` int(10) unsigned NOT NULL,
            PRIMARY KEY  (`id`),
            KEY `customer_tag_products_FKIndex1` (`customer_tag_id`),
            KEY `customer_tag_products_FKIndex2` (`product_id`),
            CONSTRAINT `FK_customer_tag_product_id` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `FK_customer_tag_product_customer` FOREIGN KEY (`customer_tag_id`) REFERENCES `{$installer->getTable('tag_customer')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        ");

        Axis::single('core/config_field')
            ->add('tag', 'Tag', null, null, array('translation_module' => 'Axis_Tag'))
            ->add('tag/main/customer_status', 'Tag/General/Default customer tag status', Axis_Tag_Model_Option_Status::APPROVED, 'select', 'Default tag status added by registered customer', array('model' => 'Axis_Tag_Model_Option_Status'))
            ->add('tag/main/guest_status', 'Default guest tag status', Axis_Tag_Model_Option_Status::PENDING, 'select', 'Default tag status added by guest', array('model' => 'Axis_Tag_Model_Option_Status'));

        Axis::single('core/page')
            ->add('tag/*/*')
            ->add('tag/index/*')
            ->add('tag/index/show-products');
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('tag_customer')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('tag_product')}`;
        ");

        Axis::single('core/config_field')->remove('tag');
        Axis::single('core/config_value')->remove('tag');

        Axis::single('core/page')
            ->remove('tag/*/*')
            ->remove('tag/index/*')
            ->remove('tag/index/show-products');

        //Axis::single('core/template_box')
        //    ->remove('Axis_Tag_Cloud')
        //    ->remove('Axis_Tag_Account')
        //    ->remove('Axis_Tag_Product');
    }
}