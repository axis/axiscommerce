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
 * @package     Axis_Catalog
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Catalog_Upgrade_0_2_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.3';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_category')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_category')}` (
          `id` mediumint(8) unsigned NOT NULL auto_increment,
          `site_id` smallint(5) unsigned NOT NULL,
          `lft` smallint(5) unsigned NOT NULL,
          `rgt` smallint(5) unsigned NOT NULL,
          `lvl` tinyint(3) unsigned NOT NULL,
          `created_on` datetime NOT NULL,
          `modified_on` datetime default NULL,
          `status` enum('enabled','disabled') default 'enabled',
          `image_base` varchar(255) NOT NULL DEFAULT '',
          `image_listing` VARCHAR(255) NOT NULL DEFAULT '',
          PRIMARY KEY  (`id`),
          KEY `i_site_id` (`site_id`),
          KEY `i_lft` (`lft`),
          KEY `i_rgt` (`rgt`),
          KEY `i_lvl` (`lvl`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        INSERT INTO `{$installer->getTable('catalog_category')}` (`id`, `site_id`, `lft`, `rgt`, `lvl`, `created_on`, `modified_on`, `status`, `image_base`, `image_listing`) VALUES
        (1, 1, 1, 2, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'enabled', '', '');

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_category_description')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_category_description')}` (
          `category_id` mediumint(8) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL,
          `name` varchar(128) NOT NULL DEFAULT '',
          `description` text NOT NULL DEFAULT '',
          `meta_title` varchar(128) NOT NULL DEFAULT '',
          `meta_description` text NOT NULL DEFAULT '',
          `meta_keyword` varchar(255) NOT NULL DEFAULT '',
          `image_base_title` VARCHAR(128) NOT NULL DEFAULT '',
          `image_listing_title` VARCHAR(128) NOT NULL DEFAULT '',
          PRIMARY KEY  (`category_id`,`language_id`),
          KEY `i_language_id` (`language_id`),
          CONSTRAINT `FK_CATALOG_CATEGORY_DESCRIPTION_CATEGORY_ID` FOREIGN KEY (`category_id`) REFERENCES `{$installer->getTable('catalog_category')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_CATALOG_CATEGORY_DESCRIPTION_LANGUAGE_ID` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_hurl')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_hurl')}` (
          `key_word` varchar(128) NOT NULL,
          `site_id` smallint(5) unsigned NOT NULL,
          `key_type` enum('c','m','p') NOT NULL,
          `key_id` mediumint(8) unsigned NOT NULL,
          PRIMARY KEY  USING BTREE (`key_word`,`site_id`),
          KEY `i_elastic_url_site_id` (`site_id`),
          KEY `i_elastic_url_key_id` (`key_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `manufacturer_id` smallint(5) unsigned default NULL,
          `quantity` decimal(15,4) unsigned NOT NULL default '0.0000',
          `sku` varchar(255) default NULL,
          `image_base` INTEGER UNSIGNED DEFAULT NULL,
          `image_listing` INTEGER UNSIGNED DEFAULT NULL,
          `image_thumbnail` INTEGER UNSIGNED DEFAULT NULL,
          `cost` DECIMAL(15,4) UNSIGNED NOT NULL DEFAULT '0.0000',
          `price` decimal(15,4) UNSIGNED NOT NULL default '0.0000',
          `date_available` date default NULL,
          `weight` decimal(10,3) default '0.000',
          `is_active` tinyint(1) unsigned NOT NULL default '0',
          `ordered` mediumint(8) unsigned NOT NULL default '0',
          `created_on` datetime NOT NULL,
          `modified_on` datetime default NULL,
          `tax_class_id` mediumint(8) unsigned default NULL,
          `viewed` mediumint(8) unsigned NOT NULL default '0',
          `new_from` DATETIME DEFAULT NULL,
          `new_to` DATETIME DEFAULT NULL,
          `featured_from` DATETIME DEFAULT NULL,
          `featured_to` DATETIME DEFAULT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `product_sku` (`sku`),
          KEY `i_manufacture_id` USING BTREE (`manufacturer_id`),
          CONSTRAINT `FK_CATALOG_PRODUCT_MANUFACTURER` FOREIGN KEY (`manufacturer_id`) REFERENCES `{$installer->getTable('catalog_product_manufacturer')}` (`id`) ON DELETE SET NULL,
          CONSTRAINT `FK_CATALOG_PRODUCT_TAX_CLASS` FOREIGN KEY (`tax_class_id`) REFERENCES `{$installer->getTable('tax_class')}` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
          CONSTRAINT `FK_CATALOG_PRODUCT_IMAGE_BASE_IMAGE` FOREIGN KEY `FK_CATALOG_PRODUCT_IMAGE_BASE_IMAGE` (`image_base`)
            REFERENCES `{$installer->getTable('catalog_product_image')}` (`id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
          CONSTRAINT `FK_CATALOG_PRODUCT_IMAGE_LISTING_IMAGE` FOREIGN KEY `FK_CATALOG_PRODUCT_IMAGE_LISTING_IMAGE` (`image_listing`)
            REFERENCES `{$installer->getTable('catalog_product_image')}` (`id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
          CONSTRAINT `FK_CATALOG_PRODUCT_IMAGE_THUMBNAIL_IMAGE` FOREIGN KEY `FK_CATALOG_PRODUCT_IMAGE_THUMBNAIL_IMAGE` (`image_thumbnail`)
            REFERENCES `{$installer->getTable('catalog_product_image')}` (`id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_attribute')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_attribute')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `product_id` int(10) unsigned NOT NULL,
          `variation_id` int(10) unsigned default '0',
          `option_id` mediumint(8) unsigned NOT NULL,
          `option_value_id` int(10) unsigned default NULL,
          `price` decimal(15,4) default NULL,
          `price_type` enum('to','by','percent') default NULL,
          `weight` decimal(10,2) default NULL,
          `weight_type` enum('to','by','percent') default NULL,
          `modifier` tinyint(1) unsigned NOT NULL default '0',
          PRIMARY KEY  (`id`),
          KEY `FK_product_attribute2_pi` (`product_id`),
          KEY `FK_product_attribute2_pvi` (`variation_id`),
          KEY `FK_product_attribute2_poi` (`option_id`),
          KEY `FK_product_attribute2_povi` (`option_value_id`),
          CONSTRAINT `FK_PRODUCT_ATTRIBUTE_OPTION_ID` FOREIGN KEY (`option_id`) REFERENCES `{$installer->getTable('catalog_product_option')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_PRODUCT_ATTRIBUTE_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_PRODUCT_ATTRIBUTE_VALUE_ID` FOREIGN KEY (`option_value_id`) REFERENCES `{$installer->getTable('catalog_product_option_value')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_PRODUCT_ATTRIBUTE_VARIATION_ID` FOREIGN KEY (`variation_id`) REFERENCES `{$installer->getTable('catalog_product_variation')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_attribute_value')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_attribute_value')}` (
          `product_attribute_id` int(10) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL default '0',
          `attribute_value` text,
          PRIMARY KEY  (`product_attribute_id`,`language_id`),
          KEY `FK_PRODUCT_ATTRIBUTE_ID` (`product_attribute_id`),
          CONSTRAINT `FK_PRODUCT_ATTRIBUTE_ID` FOREIGN KEY (`product_attribute_id`) REFERENCES `{$installer->getTable('catalog_product_attribute')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_category')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_category')}` (
          `category_id` mediumint(8) unsigned NOT NULL,
          `product_id` int(10) unsigned NOT NULL,
          PRIMARY KEY  (`category_id`,`product_id`),
          KEY `FK_PRODUCT_ID` (`product_id`),
          CONSTRAINT `FK_PRODUCT_CATEGORY_ID` FOREIGN KEY (`category_id`) REFERENCES `{$installer->getTable('catalog_category')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_description')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_description')}` (
          `product_id` int(10) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL,
          `name` varchar(255) NOT NULL DEFAULT '',
          `description` text NOT NULL DEFAULT '',
          `viewed` mediumint(8) unsigned NOT NULL default '0',
          `image_seo_name` varchar(128) NOT NULL DEFAULT '',
          `meta_title` varchar(128) NOT NULL DEFAULT '',
          `meta_description` text NOT NULL DEFAULT '',
          `meta_keyword` text NOT NULL DEFAULT '',
          `short_description` text NOT NULL DEFAULT '',
          PRIMARY KEY  (`product_id`,`language_id`),
          KEY `FK_PRODUCT_DESCRIPTION_LANGUAGE_ID` (`language_id`),
          CONSTRAINT `FK_PRODUCT_DESCRIPTION_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_PRODUCT_DESCRIPTION_LANGUAGE_ID` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_image')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_image')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `product_id` int(10) unsigned NOT NULL,
          `path` varchar(255) NOT NULL DEFAULT '',
          `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 1,
          PRIMARY KEY  (`id`),
          KEY `FK_CATALOG_PRODUCT_IMAGE_PRODUCT_ID_PRODUCT` (`product_id`),
          CONSTRAINT `FK_CATALOG_PRODUCT_IMAGE_PRODUCT_ID_PRODUCT` FOREIGN KEY (`product_id`)
            REFERENCES `{$installer->getTable('catalog_product')}` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_image_title')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_image_title')}` (
          `image_id` INTEGER UNSIGNED NOT NULL,
          `language_id` SMALLINT(5) UNSIGNED NOT NULL,
          `title` VARCHAR(128) NOT NULL,
          PRIMARY KEY (`image_id`, `language_id`),
          CONSTRAINT `FK_CATALOG_PRODUCT_IMAGE_TITLE_IMAGE` FOREIGN KEY `FK_CATALOG_PRODUCT_IMAGE_TITLE_IMAGE` (`image_id`)
            REFERENCES `{$installer->getTable('catalog_product_image')}` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
          CONSTRAINT `FK_CATALOG_PRODUCT_IMAGE_TITLE_LANGUAGE` FOREIGN KEY `FK_CATALOG_PRODUCT_IMAGE_TITLE_LANGUAGE` (`language_id`)
            REFERENCES `{$installer->getTable('locale_language')}` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_manufacturer')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_manufacturer')}` (
          `id` smallint(5) unsigned NOT NULL auto_increment,
          `name` varchar(128) NOT NULL,
          `image` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_manufacturer_title')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_manufacturer_title')}` (
          `manufacturer_id` smallint(5) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL,
          `title` varchar(255) NOT NULL,
          PRIMARY KEY  USING BTREE (`manufacturer_id`,`language_id`),
          KEY `FK_PRODUCT_MANUFACTURER_TITLE_LANGUAGE_ID` (`language_id`),
          CONSTRAINT `FK_PRODUCT_MANUFACTURER_TITLE_MANUFACTURER_ID` FOREIGN KEY (`manufacturer_id`) REFERENCES `{$installer->getTable('catalog_product_manufacturer')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_PRODUCT_MANUFACTURER_TITLE_LANGUAGE_ID` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_option')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_option')}` (
          `id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
          `code` VARCHAR(32) NOT NULL,
          `input_type` TINYINT(3) UNSIGNED NOT NULL default '0',
          `sort_order` TINYINT(3) UNSIGNED NOT NULL default '10',
          `searchable` TINYINT(1) UNSIGNED NOT NULL default '0',
          `comparable` TINYINT(1) UNSIGNED NOT NULL default '1',
          `languagable` TINYINT(1) UNSIGNED NOT NULL default '0',
          `filterable` TINYINT(1) UNSIGNED NOT NULL default '1',
          `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
          `valueset_id` SMALLINT(5) UNSIGNED DEFAULT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `product_option_code` (`code`),
          KEY `product_option_sort_order` USING BTREE (`sort_order`),
          CONSTRAINT `FK_CATALOG_PRODUCT_OPTION_VALUESET` FOREIGN KEY `FK_CATALOG_PRODUCT_OPTION_VALUESET` (`valueset_id`)
            REFERENCES `{$installer->getTable('catalog_product_option_valueset')}` (`id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_option_text')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_option_text')}` (
          `option_id` mediumint(8) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL,
          `name` varchar(128) NOT NULL,
          `description` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY  (`option_id`,`language_id`),
          CONSTRAINT `FK_PRODUCT_OPTION_ID` FOREIGN KEY (`option_id`)
            REFERENCES `{$installer->getTable('catalog_product_option')}` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
          CONSTRAINT `FK_PRODUCT_OPTION_LANGUAGE` FOREIGN KEY (`language_id`)
            REFERENCES `{$installer->getTable('locale_language')}` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_option_value')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('catalog_product_option_value')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `sort_order` tinyint(3) unsigned NOT NULL default '0',
          `valueset_id` smallint(5) unsigned NOT NULL,
          PRIMARY KEY  USING BTREE (`id`),
          KEY `FK_CATALOG_PRODUCT_OPTION_VALUE_VALUESET` (`valueset_id`),
          CONSTRAINT `FK_CATALOG_PRODUCT_OPTION_VALUE_VALUESET` FOREIGN KEY (`valueset_id`)
           REFERENCES `{$installer->getTable('catalog_product_option_valueset')}` (`id`)
           ON DELETE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_option_value_text')}`;
        CREATE TABLE  `{$installer->getTable('catalog_product_option_value_text')}` (
          `option_value_id` int(10) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL,
          `name` varchar(128) default NULL,
          PRIMARY KEY  USING BTREE (`option_value_id`,`language_id`),
          CONSTRAINT `FK_PRODUCT_OPTION_VALUE_ID` FOREIGN KEY (`option_value_id`)
            REFERENCES `{$installer->getTable('catalog_product_option_value')}` (`id`)
              ON DELETE CASCADE,
          CONSTRAINT `FK_PRODUCT_OPTION_VALUE_LANGUAGE` FOREIGN KEY (`language_id`)
            REFERENCES `{$installer->getTable('locale_language')}` (`id`)
              ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_option_valueset')}`;
        CREATE TABLE  `{$installer->getTable('catalog_product_option_valueset')}` (
          `id` smallint(5) unsigned NOT NULL auto_increment,
          `name` varchar(128) NOT NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_stock')}`;
        CREATE TABLE  `{$installer->getTable('catalog_product_stock')}` (
          `product_id` int(10) unsigned NOT NULL,
          `in_stock` int(1) NOT NULL default '1',
          `manage` int(1) NOT NULL default '1',
          `min_qty` int(8) NOT NULL default '0',
          `min_qty_allowed` int(8) NOT NULL default '1',
          `max_qty_allowed` int(8) NOT NULL default '0',
          `decimal` int(1) NOT NULL default '0',
          `notify_qty` int(8) NOT NULL default '0',
          `backorder` int(8) NOT NULL default '0',
          PRIMARY KEY  (`product_id`),
          CONSTRAINT `FK_product_stock_id` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_variation')}`;
        CREATE TABLE  `{$installer->getTable('catalog_product_variation')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `product_id` int(10) unsigned default NULL,
          `sku` varchar(255) NOT NULL,
          `quantity` decimal(15,4) unsigned NOT NULL default '0.0000',
          `cost` DECIMAL(15,4) UNSIGNED NOT NULL DEFAULT '0.0000',
          `price` decimal(15,4) NOT NULL default '0.00',
          `price_type` enum('to','by','percent') NOT NULL default 'by',
          `weight` decimal(10,2) NOT NULL default '0.00',
          `weight_type` enum('to','by','percent') NOT NULL default 'by',
          PRIMARY KEY  (`id`),
          KEY `FK_PRODUCT_VARIATION_PRODUCT_ID` (`product_id`),
          CONSTRAINT `FK_PRODUCT_VARIATION_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

        INSERT INTO `{$installer->getTable('catalog_product_variation')}` (`id`, `product_id`, `price`, `sku`, `quantity`, `price_type`, `weight`, `weight_type`) VALUES (0, NULL, 0.0000, '', 0.0000, 'by', 0.00, 'by');

        ");

        $languages = Axis::model('locale/option_language');
        $modelCatalogCategoryDescription = Axis::model('catalog/category_description');
        foreach ($languages as $languageId => $languageName) {
            $modelCatalogCategoryDescription->createRow(array(
                'category_id'   => 1,
                'language_id'   => $languageId,
                'name'          => 'Main Store',
                'description'   => 'Root Category'
            ))->save();
        }

        Axis::single('core/config_field')
            ->add('catalog', 'Catalog', null, null, array('translation_module' => 'Axis_Catalog'))

            ->add('catalog/main/route', 'Catalog/General/Catalog route', 'store', 'string', 'Catalog url (example.com/<b>route</b>/product)')

            ->add('catalog/listing/type', 'Catalog/Product Listing/Type', Axis_Catalog_Model_Option_Product_Listing_Type::getConfigOptionDeafultValue(), 'select', 'Default listing type', array('model' => 'Axis_Catalog_Model_Option_Product_Listing_Type'))
            ->add('catalog/listing/perPage', 'Show per page', '6,9,18,32')
            ->add('catalog/listing/perPageDefault', 'Default product count per page', 9)
            ->add('catalog/listing/sortBy', 'Sort By', 'Name,Price')

            ->add('catalog/product/hurldelimiter', 'Catalog/Product View/Hurl world delimiter', '-')
            ->add('catalog/product/seodelimiter', 'Hurl world delimiter', '_')
            ->add('catalog/product/seodesclength', 'SEO Description Length (45 -150 chars)', 100)

            ->add('catalog/lightzoom/zoomStageWidth', 'Catalog/Lightzoom/Zoomer width', 250)
            ->add('catalog/lightzoom/zoomStageHeight', 'Zoomer height', 250)
            ->add('catalog/lightzoom/zoomStagePosition', 'Zoomer position', Axis_Catalog_Model_Option_Lightzoom_StagePosition::getConfigOptionDeafultValue(), 'select', '', array('model' => 'Axis_Catalog_Model_Option_Lightzoom_StagePosition'))
            ->add('catalog/lightzoom/zoomStageOffsetX', 'Zoomer offset-x', 10)
            ->add('catalog/lightzoom/zoomStageOffsetY', 'Zoomer offset-y', 0)
            ->add('catalog/lightzoom/zoomLensOpacity', 'Lens opacity', 0.7)
            ->add('catalog/lightzoom/zoomCursor', 'Lens cursor', Axis_Catalog_Model_Option_Lightzoom_Cursor::getConfigOptionDeafultValue(), 'select', '', array('model' => 'Axis_Catalog_Model_Option_Lightzoom_Cursor'))
            ->add('catalog/lightzoom/zoomOnTrigger', 'Zoom on trigger', Axis_Catalog_Model_Option_Lightzoom_DomEvent_OnTrigger::getConfigOptionDeafultValue(), 'select', 'Select none, if you wish to disable this event', array('model' => 'Axis_Catalog_Model_Option_Lightzoom_DomEvent_OnTrigger'))
            ->add('catalog/lightzoom/zoomOffTrigger', 'Zoom off trigger', Axis_Catalog_Model_Option_Lightzoom_DomEvent_OffTrigger::getConfigOptionDeafultValue(), 'select', 'Select none, if you wish to disable this event', array('model' => 'Axis_Catalog_Model_Option_Lightzoom_DomEvent_OffTrigger'))
            ->add('catalog/lightzoom/lightboxTrigger', 'Lightbox trigger', Axis_Catalog_Model_Option_Lightzoom_DomEvent_Trigger::getConfigOptionDeafultValue(), 'select', 'Select none, if you wish to disable this event', array('model' => 'Axis_Catalog_Model_Option_Lightzoom_DomEvent_Trigger'))
            ->add('catalog/lightzoom/lightboxResizeSpeed', 'Lightbox resize speed', 800, 'string', 'Animation speed, ms')
            ->add('catalog/lightzoom/lightboxFadeSpeed', 'Lightbox fade speed', 300, 'string', 'Animation speed, ms')
            ->add('catalog/lightzoom/lightboxMaskOpacity', 'Mask opacity', 0.8)
            ->add('catalog/lightzoom/switchImageTrigger', 'Switch image trigger', Axis_Catalog_Model_Option_Lightzoom_DomEvent_ImageTrigger::getConfigOptionDeafultValue(), 'select', 'Select none, if you wish to disable this event', array('model' => 'Axis_Catalog_Model_Option_Lightzoom_DomEvent_ImageTrigger'))

            ->add('image', 'Images', null, null, array('translation_module' => 'Axis_Catalog'))
            ->add('image/main/cachePath', 'Images/General/Cache path', '/media/cache', 'string', 'Image cache path, relative to AXIS_ROOT')
            ->add('image/product/cache', 'Images/Product Images/Cache', 1, 'bool', 'Enable image cache', array('model'=> 'Axis_Core_Model_Option_Boolean'))
            ->add('image/product/widthLarge', 'Large width', 0)
            ->add('image/product/heightLarge', 'Large height', 0)
            ->add('image/product/widthMedium', 'Product Info width', 250)
            ->add('image/product/heightMedium', 'Product Info height', 250)
            ->add('image/product/widthSmall', 'Small width', 150)
            ->add('image/product/heightSmall', 'Small height', 150)
            ->add('image/product/widthThumbnail', 'Thumbnail image width', 40)
            ->add('image/product/heightThumbnail', 'Thumbnail image height', 40)
            ->add('image/watermark/enabled', 'Images/Watermark/Enabled', 0, 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean'))
            ->add('image/watermark/image', 'Image path', 'catalog/watermark.png', 'string', 'Path relative to the skin images folder: catalog/watermark.png')
            ->add('image/watermark/position', 'Watermark Position', Axis_Catalog_Model_Option_Watermark_Position::getConfigOptionDeafultValue(), 'select', array('model' => 'Axis_Catalog_Model_Option_Watermark_Position'))
            ->add('image/watermark/opacity', 'Opacity', 50, 'string', 'Values [0 - 100]')
            ->add('image/watermark/repeat', 'Repeat', 0, 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean'));

        Axis::single('core/page')
            ->add('catalog/*/*')
            ->add('catalog/index/*')
            ->add('catalog/index/product')
            ->add('catalog/index/view')
            ->add('catalog/product-compare/*')
            ->add('catalog/product-compare/index');
    }
}