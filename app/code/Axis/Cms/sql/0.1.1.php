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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */


class Axis_Cms_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_block')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_block')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `content` text,
          `is_active` tinyint(1) default NULL,
          `name` varchar(128) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=3;

        INSERT INTO `{$installer->getTable('cms_block')}`
        (`id`, `content`, `is_active`, `name`) VALUES
        (1, '<noscript><div class=\"noscript-notice\"><p><strong>{{helper_t(Please enable JavaScript in your browser.)}}</strong></p></div></noscript>', 1 , 'noscript_notice'),
        (2, '<div class=\"demo-notice\">{{helper_t(Please notice! This is a demo store. Any order placed will not be processed.)}}</div>', 1, 'demo_notice'),
        (3, '<p class=\"legality\">&copy; 2008-2010 <a href=\"http://www.axiscommerce.com\">Axis</a> {{helper_t(Demo Store. All rights reserved.)}}</p>', 1, 'copyright');

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_category')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_category')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `is_active` tinyint(3) unsigned default '1',
          `parent_id` int(10) unsigned default NULL,
          `site_id` smallint(5) unsigned default NULL,
          `name` varchar(128) NOT NULL,
          PRIMARY KEY  USING BTREE (`id`),
          KEY `fk_cms_category_cms_category` (`parent_id`),
          KEY `FK_cms_category_site_id` (`site_id`),
          CONSTRAINT `FK_cms_category_site_id` FOREIGN KEY (`site_id`) REFERENCES `{$installer->getTable('core_site')}` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
          CONSTRAINT `fk_cms_category_cms_category` FOREIGN KEY (`parent_id`) REFERENCES `{$installer->getTable('cms_category')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=3;

        INSERT INTO `{$installer->getTable('cms_category')}` (`id`, `is_active`, `parent_id`, `site_id`, `name`) VALUES
          (1, 1, NULL, 1, 'General'),
          (2, 1, NULL, 1, 'about-us');

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_category_content')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_category_content')}` (
          `cms_category_id` int(10) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL,
          `title` varchar(45) default NULL,
          `description` text,
          `link` varchar(128) default NULL,
          `meta_keyword` text,
          `meta_description` text,
          `meta_title` varchar(128) default NULL,
          PRIMARY KEY  USING BTREE (`language_id`,`cms_category_id`),
          KEY `fk_cms_category_title_cms_category` (`cms_category_id`),
          CONSTRAINT `FK_cms_category_content_language` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `fk_cms_category_title_cms_category` FOREIGN KEY (`cms_category_id`) REFERENCES `{$installer->getTable('cms_category')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        INSERT INTO `{$installer->getTable('cms_category_content')}` (`cms_category_id`, `language_id`, `title`, `description`, `link`, `meta_keyword`, `meta_description`, `meta_title`) VALUES
          (1, 1, '', 'description', 'general', '', '', 'general'),
          (2, 1, 'About us', 'about our company', 'about-us', '', '', '');

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_page')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_page')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `is_active` tinyint(4) default NULL,
          `layout` varchar(45) default NULL,
          `comment` tinyint(4) default NULL,
          `access` tinyint(4) default NULL,
          `name` varchar(128) default NULL,
          `show_in_box` tinyint(1) unsigned NOT NULL default '0',
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        INSERT INTO `{$installer->getTable('cms_page')}` (`id`, `is_active`, `layout`, `comment`, `access`, `name`, `show_in_box`) VALUES
          (1, 1, 'default_2columns-left', 1, NULL, 'Privacy policy', 1),
          (2, 1, 'default_1column', 1, NULL, 'Shipping and returns', 1),
          (3, 1, 'default_2columns-right', 1, NULL, 'Terms of Use', 1),
          (4, 1, 'default_2columns-left', 1, NULL, 'General', 0),
          (5, 1, 'template testing_2columns-left', 0, NULL, 'company-history', 0),
          (6, 1, 'default_2columns-left', 1, NULL, 'careers', 0);

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_page_content')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_page_content')}` (
          `cms_page_id` int(10) unsigned NOT NULL,
          `language_id` smallint(5) unsigned NOT NULL,
          `link` varchar(128) default NULL,
          `title` varchar(128) default NULL,
          `content` text,
          `meta_keyword` text,
          `meta_description` text,
          `meta_title` varchar(128) default NULL,
          PRIMARY KEY  USING BTREE (`language_id`,`cms_page_id`),
          KEY `FK_cms_page_content_cms_page` (`cms_page_id`),
          CONSTRAINT `FK_cms_page_content_language` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_cms_page_content_cms_page` FOREIGN KEY (`cms_page_id`) REFERENCES `{$installer->getTable('cms_page')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        INSERT INTO `{$installer->getTable('cms_page_content')}` (`cms_page_id`, `language_id`, `link`, `title`, `content`, `meta_keyword`, `meta_description`, `meta_title`) VALUES
          (1, 1, 'privacy', 'Privacy policy', '<div class=\"col2-set\">\n    <div class=\"col-1\">asd, consectetuer adipiscing elit. Sed dolor urna, dapibus ac, convallis eget, ornare a, nisl. Quisque vestibulum congue est. Vivamus ante. Nullam neque tellus, aliquet sed, placerat eget, sagittis hendrerit, leo. Fusce varius pulvinar pede. Fusce at lectus. Nunc ac purus. Aenean rhoncus lacinia nisl. Sed eros sapien, pretium ut, condimentum at, lobortis porta, leo. Aenean ut nibh non metus porttitor sodales. Suspendisse nisl. Phasellus condimentum egestas magna.\nFusce porta porttitor enim. Aenean tempor est nec massa. Phasellus in sapien. Vestibulum urna odio, imperdiet eu, faucibus eu, pretium a, arcu. Morbi mi urna, commodo in, eleifend id, interdum sed, lorem. Pellentesque ullamcorper purus in sapien. Integer faucibus quam a leo. Vivamus posuere porta ipsum. In purus. Proin commodo. Cras eget mi in lacus dignissim volutpat. Fusce orci. Donec eget erat. Mauris sapien libero, sodales non, consequat ut, varius id, lorem.\nMorbi adipiscing. Aenean eu mi. Praesent erat lectus, fringilla non, condimentum ut, semper vitae, lectus. Ut vulputate. Vivamus purus velit, semper nec, dignissim vel, faucibus nec, felis. Cras posuere. Curabitur dignissim convallis lorem. Aenean bibendum auctor justo. Nulla nec diam vel justo rutrum tempor. Suspendisse nec tortor a eros laoreet rutrum. In sapien.\n</div>\n<div class=\"col-2\">\nCras sem sem, condimentum sed, bibendum ac, molestie eget, felis. Integer diam pede, pulvinar quis, eleifend vel, pretium at, nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque id lectus vitae augue imperdiet sodales. Donec dapibus nisl nec arcu. Ut felis turpis, accumsan ac, sagittis a, aliquet vitae, leo. Nullam venenatis, leo quis consectetuer ullamcorper, nisl justo bibendum lorem, sit amet pulvinar quam lorem nec sem. Nunc eleifend, quam sed viverra sollicitudin, dui arcu aliquet nibh, nec tincidunt sem quam non ante. Maecenas et lectus. In tincidunt nisl et velit. Nam venenatis, augue eget congue gravida, mi neque facilisis nunc, sed porta augue erat ac felis\nProin tellus risus, pulvinar ac, pretium eu, faucibus eu, ipsum. Fusce mauris nisl, elementum id, auctor sit amet, porta ac, nibh. Mauris vulputate egestas ipsum.\n\nNunc eget nisi. Phasellus id elit nec elit sollicitudin imperdiet. Cras a justo. Praesent orci. Vivamus sagittis libero ut nulla. Integer dapibus lectus quis lorem. Maecenas consectetuer urna vitae lectus volutpat malesuada. Duis risus. Sed vulputate nulla ac nibh. Pellentesque tincidunt pharetra turpis. Cras libero velit, tristique ut, vehicula a, tempor in, nibh. Praesent ac magna at risus lobortis dictum. Curabitur ultrices neque vehicula neque.</div>\n</div>', 'privacy', 'description of privacy policy page', 'Privacy policy'),
          (2, 1, 'shipping', 'Shipping and returns', 'Praesent vestibulum iaculis eros. Donec porta odio in tortor. Proin nulla nunc, ornare eu, rhoncus non, laoreet quis, sem. Praesent dictum, sapien a fermentum adipiscing, erat mauris dignissim nisi, <span style=\"background-color: rgb(153, 204, 0);\">nec placerat lorem quam id est.<br><br><span style=\"background-color: rgb(192, 192, 192);\">Nullam ut libero. Fusce libero magna, iaculis ac, tempus nec, posuere id, felis. Nulla suscipit augue in sapien. Nullam congue convallis dolor. Cras gravida felis vel nulla. Etiam pulvinar sem in nisi ornare mattis. Proin tempus.</span></span><span style=\"background-color: rgb(192, 192, 192);\"> </span><br><br>Phasellus tincidunt mattis nunc. Fusce lorem. Sed consequat. Nulla ac purus. Donec vel nibh. Aliquam in sapien. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. In eget quam.', '', '', ''),
          (3, 1, 'terms-of-use', 'Terms of use', '{{static_intro}}<br>\n<p>\nIt is a long established fact that a reader will be distracted by the\nreadable content of a page when looking at its layout. The point of\nusing  is that it has a more-or-less normal distribution of\nletters, as opposed to using \"Content here, content here\", making it\nlook like readable English.</p>&nbsp;{{static_google_banner}}\n<br>\n<p>\nMany desktop publishing packages and web\npage editors now use Lorem Ipsum as their default model text, and a\nsearch for \"lorem ipsum\" will uncover many web sites still in their\ninfancy. Various versions have evolved over the years, sometimes by\naccident, sometimes on purpose (injected humour and the like).\n</p>\n{{static_name_new}}', '', '', ''),
          (4, 1, 'general', NULL, '', '', '', ''),
          (5, 1, 'company-history', 'Company history', '<div class=\"col3-set\">\n    <div class=\"col-1\">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed dolor urna, dapibus ac, convallis eget, ornare a, nisl. Quisque vestibulum congue est. Vivamus ante. Nullam neque tellus, aliquet sed, placerat eget, sagittis hendrerit, leo. Fusce varius pulvinar pede. Fusce at lectus. Nunc ac purus. Aenean rhoncus lacinia nisl. Sed eros sapien, pretium ut, condimentum at, lobortis porta, leo. Aenean ut nibh non metus porttitor sodales. Suspendisse nisl. Phasellus condimentum egestas magna.\nFusce porta porttitor enim. Aenean tempor est nec massa. Phasellus in sapien. Vestibulum urna odio, imperdiet eu, faucibus eu, pretium a, arcu. Morbi mi urna, commodo in, eleifend id, interdum sed, lorem. Pellentesque ullamcorper purus in sapien. Integer faucibus quam a leo. Vivamus posuere porta ipsum. In purus. Proin commodo. Cras eget mi in lacus dignissim volutpat. Fusce orci. Donec eget erat. Mauris sapien libero, sodales non, consequat ut, varius id, lorem.\n</div>\n  <div class=\"col-2\">\nMorbi adipiscing. Aenean eu mi. Praesent erat lectus, fringilla non, condimentum ut, semper vitae, lectus. Ut vulputate. Vivamus purus velit, semper nec, dignissim vel, faucibus nec, felis. Cras posuere. Curabitur dignissim convallis lorem. Aenean bibendum auctor justo. Nulla nec diam vel justo rutrum tempor. Suspendisse nec tortor a eros laoreet rutrum. In sapien.\nCras sem sem, condimentum sed, bibendum ac, molestie eget, felis. Integer diam pede, pulvinar quis, eleifend vel, pretium at, nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque id lectus vitae augue imperdiet sodales. Donec dapibus nisl nec arcu. Ut felis turpis, accumsan ac, sagittis a, aliquet vitae, leo. Nullam venenatis, leo quis consectetuer ullamcorper, nisl justo bibendum lorem, sit amet pulvinar quam lorem nec sem. Nunc eleifend, quam sed viverra sollicitudin, dui arcu aliquet nibh, nec tincidunt sem quam non ante. Maecenas et lectus. In tincidunt nisl et velit. Nam venenatis, augue eget congue gravida, mi neque facilisis nunc, sed porta augue erat ac felis. </div>\n<div class=\"col-3\">Proin tellus risus, pulvinar ac, pretium eu, faucibus eu, ipsum. Fusce mauris nisl, elementum id, auctor sit amet, porta ac, nibh. Mauris vulputate egestas ipsum.\n\nNunc eget nisi. Phasellus id elit nec elit sollicitudin imperdiet. Cras a justo. Praesent orci. Vivamus sagittis libero ut nulla. Integer dapibus lectus quis lorem. Maecenas consectetuer urna vitae lectus volutpat malesuada. Duis risus. Sed vulputate nulla ac nibh. Pellentesque tincidunt pharetra turpis. Cras libero velit, tristique ut, vehicula a, tempor in, nibh. Praesent ac magna at risus lobortis dictum. Curabitur ultrices neque vehicula neque.</div>\n</div>', 'Company, about, history', '', 'Company history'),
          (6, 1, 'careers', 'Careers at our store', '<div class=\"col2-set\" style=\"margin-bottom: 7px;\">\n <div class=\"col-1\">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed dolor urna, dapibus ac, convallis eget, ornare a, nisl. Quisque vestibulum congue est. Vivamus ante. Nullam neque tellus, aliquet sed, placerat eget, sagittis hendrerit, leo. Fusce varius pulvinar pede. Fusce at lectus. Nunc ac purus. Aenean rhoncus lacinia nisl. Sed eros sapien, pretium ut, condimentum at, lobortis porta, leo. Aenean ut nibh non metus porttitor sodales. Suspendisse nisl. Phasellus condimentum egestas magna. </div>\n <div class=\"col-2\">Fusce porta porttitor enim. Aenean tempor est nec massa. Phasellus in sapien. Vestibulum urna odio, imperdiet eu, faucibus eu, pretium a, arcu. Morbi mi urna, commodo in, eleifend id, interdum sed, lorem. Pellentesque ullamcorper purus in sapien. Integer faucibus quam a leo. Vivamus posuere porta ipsum. In purus. Proin commodo. Cras eget mi in lacus dignissim volutpat. Fusce orci. Donec eget erat. Mauris sapien libero, sodales non, consequat ut, varius id, lorem. </div>\n</div>\n<div>\nCras sem sem, condimentum sed, bibendum ac, molestie eget, felis. Integer diam pede, pulvinar quis, eleifend vel, pretium at, nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque id lectus vitae augue imperdiet sodales. Donec dapibus nisl nec arcu. Ut felis turpis, accumsan ac, sagittis a, aliquet vitae, leo. Nullam venenatis, leo quis consectetuer ullamcorper, nisl justo bibendum lorem, sit amet pulvinar quam lorem nec sem. Nunc eleifend, quam sed viverra sollicitudin, dui arcu aliquet nibh, nec tincidunt sem quam non ante. Maecenas et lectus. In tincidunt nisl et velit. Nam venenatis, augue eget congue gravida, mi neque facilisis nunc, sed porta augue erat ac felis. Proin tellus risus, pulvinar ac, pretium eu, faucibus eu, ipsum. Fusce mauris nisl, elementum id, auctor sit amet, porta ac, nibh. Mauris vulputate egestas ipsum.\n\nNunc eget nisi. Phasellus id elit nec elit sollicitudin imperdiet. Cras a justo. Praesent orci. Vivamus sagittis libero ut nulla. Integer dapibus lectus quis lorem. Maecenas consectetuer urna vitae lectus volutpat malesuada. Duis risus. Sed vulputate nulla ac nibh. Pellentesque tincidunt pharetra turpis. Cras libero velit, tristique ut, vehicula a, tempor in, nibh. Praesent ac magna at risus lobortis dictum. Curabitur ultrices neque vehicula neque. \n</div>', '', '', '');

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_page_category')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_page_category')}` (
          `cms_category_id` int(10) unsigned NOT NULL,
          `cms_page_id` int(10) unsigned NOT NULL,
          PRIMARY KEY  (`cms_category_id`,`cms_page_id`),
          KEY `fk_cms_page_to_category_cms_category` (`cms_category_id`),
          KEY `fk_cms_page_to_category_cms_page` (`cms_page_id`),
          CONSTRAINT `fk_cms_page_to_category_cms_category` FOREIGN KEY (`cms_category_id`) REFERENCES `{$installer->getTable('cms_category')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `fk_cms_page_to_category_cms_page` FOREIGN KEY (`cms_page_id`) REFERENCES `{$installer->getTable('cms_page')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        INSERT INTO `{$installer->getTable('cms_page_category')}` (`cms_category_id`, `cms_page_id`) VALUES
          (1, 1),
          (1, 2),
          (1, 3),
          (1, 4),
          (2, 5),
          (2, 6);

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_page_comment')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_page_comment')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `cms_page_id` int(10) unsigned NOT NULL,
          `author` varchar(128) default NULL,
          `created_on` datetime default NULL,
          `modified_on` datetime default NULL,
          `content` text,
          `status` tinyint(3) unsigned default NULL,
          `email` varchar(128) default NULL,
          PRIMARY KEY  USING BTREE (`id`),
          KEY `fk_cms_page_comment_cms_page` (`cms_page_id`),
          CONSTRAINT `fk_cms_page_comment_cms_page` FOREIGN KEY (`cms_page_id`) REFERENCES `{$installer->getTable('cms_page')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        ");

        Axis::single('admin/acl_resource')
            ->add('admin/cms', 'CMS')
            ->add('admin/cms_index', 'Categories/Pages')
            ->add("admin/cms_index/copy-page")
            ->add("admin/cms_index/delete-category")
            ->add("admin/cms_index/delete-page")
            ->add("admin/cms_index/get-category")
            ->add("admin/cms_index/get-page-data")
            ->add("admin/cms_index/get-pages")
            ->add("admin/cms_index/get-site-tree")
            ->add("admin/cms_index/index")
            ->add("admin/cms_index/move-category")
            ->add("admin/cms_index/quick-save-page")
            ->add("admin/cms_index/save-category")
            ->add("admin/cms_index/save-page")

            ->add('admin/cms_block', 'Static Blocks')
            ->add("admin/cms_block/delete-block")
            ->add("admin/cms_block/get-block-data")
            ->add("admin/cms_block/get-blocks")
            ->add("admin/cms_block/index")
            ->add("admin/cms_block/quick-save-block")
            ->add("admin/cms_block/save-block")

            ->add('admin/cms_comment', 'Page Comments')
            ->add("admin/cms_comment/delete-comment")
            ->add("admin/cms_comment/get-comments")
            ->add("admin/cms_comment/get-page-tree")
            ->add("admin/cms_comment/index")
            ->add("admin/cms_comment/quick-save")
            ->add("admin/cms_comment/save-comment");

        Axis::single('admin/menu')
            ->add('CMS', null, 90, 'Axis_Cms')
            ->add('CMS->Categories/Pages', 'cms_index', 10)
            ->add('CMS->Static Blocks', 'cms_block', 20)
            ->add('CMS->Page Comments', 'cms_comment', 30);

        Axis::single('core/page')
            ->add('cms/*/*');

        Axis::single('core/template_box')
            ->add('Axis_Cms_Block_noscript_notice', 'afterBodyBegin')
            ->add('Axis_Cms_Block_demo_notice', 'afterBodyBegin');
    }

    public function down()
    {

    }
}