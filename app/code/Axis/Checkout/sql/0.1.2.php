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
 * @package     Axis_Checkout
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Checkout_Upgrade_0_1_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.2';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('checkout_cart')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('checkout_cart')}` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `customer_id` int(10) unsigned DEFAULT NULL,
          `site_id` smallint(5) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          KEY `INDEX_CHECKOUT_CART_CUSTOMER` USING BTREE (`customer_id`),
          KEY `INDEX_CHECKOUT_CART_SITE` USING BTREE (`site_id`),
          CONSTRAINT `FK_CHECKOUT_CART_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

        -- DROP TABLE IF EXISTS `{$installer->getTable('checkout_cart_product')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('checkout_cart_product')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `shopping_cart_id` int(10) unsigned NOT NULL,
          `product_id` int(10) unsigned NOT NULL,
          `quantity` decimal(15,4) unsigned NOT NULL,
          `final_price` decimal(15,4) NOT NULL,
          `variation_id` int(10) unsigned default NULL,
          `final_weight` decimal(10,4) NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `INDEX_CHECKOUT_CART_PRODUCT_PRODUCT` (`product_id`),
          KEY `INDEX_CHECKOUT_CART_PRODUCT_CART` USING BTREE (`shopping_cart_id`),
          CONSTRAINT `FK_CHECKOUT_CART_PRODUCT_CART` FOREIGN KEY (`shopping_cart_id`) REFERENCES `{$installer->getTable('checkout_cart')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_CHECKOUT_CART_PRODUCT_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('checkout_cart_product_attribute')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('checkout_cart_product_attribute')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `shopping_cart_product_id` int(10) unsigned NOT NULL,
          `product_attribute_value` text,
          `product_attribute_id` int(10) unsigned NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `INDEX_CHECKOUT_CART_PRODUCT_ATTRIBUTE_CART_PRODUCT` USING BTREE (`shopping_cart_product_id`),
          KEY `INDEX_CHECKOUT_CART_PRODUCT_ATTRIBUTE_PRODUCT_ATTRIBUTE` (`product_attribute_id`),
          CONSTRAINT `FK_CHECKOUT_CART_PRODUCT_ATTRIBUTE_CART_PRODUCT` FOREIGN KEY (`shopping_cart_product_id`) REFERENCES `{$installer->getTable('checkout_cart_product')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_CHECKOUT_CART_PRODUCT_ATTRIBUTE_PRODUCT_ATTRIBUTE` FOREIGN KEY (`product_attribute_id`) REFERENCES `{$installer->getTable('catalog_product_attribute')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        ");

        Axis::single('core/cache')
            ->add('order_total_methods', 1);

        Axis::single('core/page')
            ->add('checkout/*/*')
            ->add('checkout/cart/*')
            ->add('checkout/cart/index')
            ->add('checkout/onepage/*')
            ->add('checkout/wizard/*')
            ->add('checkout/index/success');

        Axis::single('core/config_field')
            ->add('orderTotal', 'Order Total Modules', null, null, array('translation_module' => 'Axis_Checkout'))
            ->add('orderTotal/subtotal/enabled', 'Order Total Modules/Shipping/Enabled', 1, 'bool')
            ->add('orderTotal/subtotal/sortOrder', 'Sort Order', '10')
            ->add('orderTotal/shipping/enabled', 'Order Total Modules/Subtotal/Enabled', 1, 'bool')
            ->add('orderTotal/shipping/sortOrder', 'Sort Order', '20')
            ->add('orderTotal/tax/enabled', 'Order Total Modules/Tax/Enabled', 1, 'bool')
            ->add('orderTotal/tax/sortOrder', 'Sort Order', '30')
            ->add('orderTotal/shipping_tax/enabled', 'Order Total Modules/ShippingTax/Enabled', 1, 'bool')
            ->add('orderTotal/shipping_tax/sortOrder', 'Sort Order', '40');
    }
}