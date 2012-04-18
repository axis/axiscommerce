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
 * @package     Axis_Community
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Community_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('community_media')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('community_media')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `path` varchar(255) NOT NULL,
          `product_id` int(10) unsigned default NULL,
          `customer_id` int(10) unsigned default NULL,
          `status` enum('pending','approved','disapproved') NOT NULL,
          `size` double NOT NULL,
          `date_uploaded` datetime NOT NULL,
          `author` varchar(128) NOT NULL,
          `title` varchar(128) NOT NULL,
          `description` varchar(255) NOT NULL,
          `media_type` enum('video','image') NOT NULL,
          `width` smallint(5) unsigned NOT NULL default '0',
          `height` smallint(5) unsigned NOT NULL default '0',
          PRIMARY KEY  (`id`),
          KEY `FK_community_media_product` (`product_id`),
          KEY `FK_community_media_customer` (`customer_id`),
          CONSTRAINT `FK_community_media_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
          CONSTRAINT `FK_community_media_product` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('community_review')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('community_review')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `product_id` int(10) unsigned NOT NULL,
          `customer_id` int(10) unsigned default NULL,
          `status` enum('pending','approved','disapproved') NOT NULL,
          `summary` text NOT NULL,
          `author` varchar(128) NOT NULL,
          `title` varchar(128) NOT NULL,
          `date_created` datetime NOT NULL,
          `pros` varchar(255) NOT NULL,
          `cons` varchar(255) NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `FK_community_review_customer` (`customer_id`),
          CONSTRAINT `FK_community_review_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
          CONSTRAINT `FK_community_review_product` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('community_review_mark')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('community_review_mark')}` (
          `review_id` int(10) unsigned NOT NULL default '0',
          `rating_id` int(10) unsigned NOT NULL,
          `mark` float default NULL,
          PRIMARY KEY  (`review_id`,`rating_id`),
          KEY `FK_community_review_mark_rating` (`rating_id`),
          CONSTRAINT `FK_community_review_mark_review` FOREIGN KEY (`review_id`) REFERENCES `{$installer->getTable('community_review')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_community_review_mark_rating` FOREIGN KEY (`rating_id`) REFERENCES `{$installer->getTable('community_review_rating')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('community_review_rating')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('community_review_rating')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `name` varchar(64) NOT NULL,
          `status` enum('enabled','disabled') NOT NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

        INSERT INTO `{$installer->getTable('community_review_rating')}` (`id`, `status`, `name`) VALUES
        (1, 'enabled', 'price'),
        (2, 'enabled', 'quality'),
        (3, 'enabled', 'value');

        -- DROP TABLE IF EXISTS `{$installer->getTable('community_review_rating_title')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('community_review_rating_title')}` (
          `rating_id` int(10) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL,
          `title` varchar(128) NOT NULL,
          PRIMARY KEY  USING BTREE (`rating_id`,`language_id`),
          KEY `FK_community_review_rating_title_language_id` (`language_id`),
          CONSTRAINT `FK_community_review_rating_title_language_id` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_community_review_rating_title_rating_id` FOREIGN KEY (`rating_id`) REFERENCES `{$installer->getTable('community_review_rating')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

        ");

        $titles = array(
            'Price',
            'Quality',
            'Value'
        );
        $languages    = Axis::model('locale/option_language');
        $mRatingTitle = Axis::model('community/review_rating_title');
        foreach (Axis::model('community/review_rating')->fetchAll() as $rating) {
            foreach ($languages as $languageId => $languageName) {
                $mRatingTitle->createRow(array(
                    'rating_id'   => $rating->id,
                    'language_id' => $languageId,
                    'title'       => $titles[$rating->id - 1]
                ))->save();
            }
        }
        
        Axis::single('core/config_builder')
            ->section('community', 'Community')
            ->setTranslation('Axis_Community')
            ->section('review', 'Reviews')
                ->option('enabled', 'Enabled', 1)
                    ->setType('radio')
                    ->setModel('core/option_boolean')
                ->option('rating_enabled', 'Enable ratings', 1)
                    ->setType('radio')
                    ->setDescription('Enable rating system in reviews')
                    ->setModel('core/option_boolean')
                ->option('rating_title', 'Show rating title', 1)
                    ->setType('radio')
                    ->setDescription('Show rating titles')
                    ->setModel('core/option_boolean')
                ->option('merge_average', 'Merge average ratings', 1)
                    ->setType('radio')
                    ->setDescription('Show average rating as one')
                    ->setModel('core/option_boolean')
                ->option('customer_status', 'Default customer review status', 'pending')
                    ->setType('select')
                    ->setDescription('Default review status written by registered customer')
                    ->setModel('community/option_review_status')
                ->option('guest_status', 'Default guest review status', 'pending')
                    ->setType('select')
                    ->setDescription('Default review status written by guest')
                    ->setModel('community/option_review_status')
                ->option('guest_permission', 'Allow guest to write a reviews', 1)
                    ->setType('radio')
                    ->setModel('core/option_boolean')
                ->option('perPage', 'Reviews showed per page', '10,25,50,all')
                ->option('perPageDefault', 'Default reviews count per page', '10')
            ->section('/review')
            /*
            ->section('image', 'Images')
                ->option('enabled', 'Enabled', 1)
                    ->setType('radio')
                    ->setDescription('Community images module status')
                    ->setModel('core/option_boolean')
                ->option('customer_status', 'Default customers image status', 'approved')
                    ->setType('select')
                    ->setDescription('Default image status uploaded by registered customer')
                    ->setModel('community/option_review_status')
                ->option('guest_status', 'Default guest image status', 'approved')
                    ->setType('select')
                    ->setDescription('Default image status uploaded by guest')
                    ->setModel('community/option_review_status')
                ->option('guest_permission', 'Allow guest to upload an images', 1)
                    ->setType('radio')
                    ->setModel('core/option_boolean')
                ->option('max_size', 'Maximum image size', '1')
                    ->setDescription('Maximum image size, allowed to upload (Mb)')
            ->section('/image')
            ->section('video', 'Videos')
                ->option('enabled', 'Enabled', 1)
                    ->setType('radio')
                    ->setDescription('Community video module status')
                    ->setModel('core/option_boolean')
                ->option('customer_status', 'Default customers video status', 'approved')
                    ->setType('select')
                    ->setDescription('Default status of video uploaded by registered customer')
                    ->setModel('community/option_review_status')
                ->option('guest_status', 'Default guest video status', 'approved')
                    ->setType('select')
                    ->setDescription('Default status of video uploaded by guest')
                    ->setModel('community/option_review_status')
                ->option('guest_permission', 'Allow guest to upload videos', 1)
                    ->setType('radio')
                    ->setDescription('Allow guest to upload videos')
                    ->setModel('core/option_boolean')
                ->option('max_size', 'Maximum video size', '5')
                    ->setDescription('Maximum video size, allowed to upload (Mb)')
            */
        ->section('/');

        Axis::single('account/customer_field')
            ->add(array('nickname' => 'Nickname'), 
                array('community' => 'Community'), 
                array(
                    'validator' => 'Alnum', 
                    'axis_validator' => 'Axis_Community_Validate_Nickname'
                )
            );

        Axis::single('core/page')
            ->add('community/*/*')
            ->add('community/review/*')
            ->add('community/review/index')
            ->add('community/review/detail')
            ->add('community/review/product')
            ->add('community/review/customer');
            /*->add('community/image/*')
            ->add('community/image/index')
            ->add('community/image/detail')
            ->add('community/image/product')
            ->add('community/image/customer')
            ->add('community/video/*')
            ->add('community/video/index')
            ->add('community/video/detail')
            ->add('community/video/product')
            ->add('community/video/customer');*/
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('community_media')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('community_review')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('community_review_mark')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('community_review_rating')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('community_review_rating_title')}`;
        ");

        Axis::single('core/config_builder')
            ->remove('community')
            ->remove('nickname');
        //Axis::single('core/template_box')->remove('Axis_Community_ReviewProduct');
    }
}