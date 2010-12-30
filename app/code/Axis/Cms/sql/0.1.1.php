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
 * @package     Axis_Cms
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_category')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_category')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `is_active` tinyint(3) unsigned default '1',
          `parent_id` int(10) unsigned default NULL,
          `site_id` smallint(5) unsigned default NULL,
          `name` varchar(128) NOT NULL,
          PRIMARY KEY  USING BTREE (`id`),
          KEY `FK_CMS_CATEGORY_CMS_CATEGORY` (`parent_id`),
          KEY `FK_CMS_CATEGORY_SITE_ID` (`site_id`),
          CONSTRAINT `FK_CMS_CATEGORY_SITE_ID` FOREIGN KEY (`site_id`) REFERENCES `{$installer->getTable('core_site')}` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
          CONSTRAINT `FK_CMS_CATEGORY_CMS_CATEGORY` FOREIGN KEY (`parent_id`) REFERENCES `{$installer->getTable('cms_category')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

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
          KEY `FK_CMS_CATEGORY_TITLE_CMS_CATEGORY` (`cms_category_id`),
          CONSTRAINT `FK_CMS_CATEGORY_CONTENT_LANGUAGE` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_CMS_CATEGORY_TITLE_CMS_CATEGORY` FOREIGN KEY (`cms_category_id`) REFERENCES `{$installer->getTable('cms_category')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
          KEY `FK_CMS_PAGE_CONTENT_CMS_PAGE` (`cms_page_id`),
          CONSTRAINT `FK_CMS_PAGE_CONTENT_LANGUAGE` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_CMS_PAGE_CONTENT_CMS_PAGE` FOREIGN KEY (`cms_page_id`) REFERENCES `{$installer->getTable('cms_page')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_page_category')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_page_category')}` (
          `cms_category_id` int(10) unsigned NOT NULL,
          `cms_page_id` int(10) unsigned NOT NULL,
          PRIMARY KEY  (`cms_category_id`,`cms_page_id`),
          KEY `FK_CMS_PAGE_TO_CATEGORY_CMS_CATEGORY` (`cms_category_id`),
          KEY `FK_CMS_PAGE_TO_CATEGORY_CMS_PAGE` (`cms_page_id`),
          CONSTRAINT `FK_CMS_PAGE_TO_CATEGORY_CMS_CATEGORY` FOREIGN KEY (`cms_category_id`) REFERENCES `{$installer->getTable('cms_category')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_CMS_PAGE_TO_CATEGORY_CMS_PAGE` FOREIGN KEY (`cms_page_id`) REFERENCES `{$installer->getTable('cms_page')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
          KEY `FK_CMS_PAGE_COMMENT_CMS_PAGE` (`cms_page_id`),
          CONSTRAINT `FK_CMS_PAGE_COMMENT_CMS_PAGE` FOREIGN KEY (`cms_page_id`) REFERENCES `{$installer->getTable('cms_page')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        ");

        //blocks
        $mBlock = Axis::model('cms/block');
        $mBlock->insert(array(
            'content'   => '<p class="legality">&copy; 2008-2010 <a href="http://www.axiscommerce.com">Axis</a>Demo Store. All rights reserved.</p>',
            'is_active' => 1,
            'name'      => 'copyright'
        ));

        // pages
        $mPage = Axis::model('cms/page');
        $privacyPageId = $mPage->insert(array(
            'is_active'     => 1,
            'layout'        => 'default_2columns-left',
            'comment'       => 1,
            'name'          => 'privacy-policy',
            'show_in_box'   => 1
        ));
        $shippingPageId = $mPage->insert(array(
            'is_active'     => 1,
            'layout'        => 'default_1column',
            'comment'       => 1,
            'name'          => 'shipping-and-returns',
            'show_in_box'   => 1
        ));
        $termsPageId = $mPage->insert(array(
            'is_active'     => 1,
            'layout'        => 'default_2columns-right',
            'comment'       => 1,
            'name'          => 'terms-of-use',
            'show_in_box'   => 1
        ));
        $historyPageId = $mPage->insert(array(
            'is_active'     => 1,
            'layout'        => 'default_2columns-left',
            'comment'       => 0,
            'name'          => 'company_history',
            'show_in_box'   => 0
        ));
        $careersPageId = $mPage->insert(array(
            'is_active'     => 1,
            'layout'        => 'default_2columns-left',
            'comment'       => 0,
            'name'          => 'careers',
            'show_in_box'   => 0
        ));

        // categories
        $mCategory = Axis::model('cms/category');
        $generalCatId = $mCategory->insert(array(
            'is_active' => 1,
            'parent_id' => new Zend_Db_Expr('NULL'),
            'site_id'   => Axis::getSiteId(),
            'name'      => 'General'
        ));
        $aboutCatId = $mCategory->insert(array(
            'is_active' => 1,
            'parent_id' => new Zend_Db_Expr('NULL'),
            'site_id'   => Axis::getSiteId(),
            'name'      => 'about-us'
        ));

        // page to category relations
        $mPageCategory = Axis::model('cms/page_category');
        $mPageCategory->insert(array(
            'cms_category_id'   => $generalCatId,
            'cms_page_id'       => $privacyPageId
        ));
        $mPageCategory->insert(array(
            'cms_category_id'   => $generalCatId,
            'cms_page_id'       => $shippingPageId
        ));
        $mPageCategory->insert(array(
            'cms_category_id'   => $generalCatId,
            'cms_page_id'       => $termsPageId
        ));
        $mPageCategory->insert(array(
            'cms_category_id'   => $generalCatId,
            'cms_page_id'       => $historyPageId
        ));
        $mPageCategory->insert(array(
            'cms_category_id'   => $aboutCatId,
            'cms_page_id'       => $careersPageId
        ));

        // content
        $mCategoryContent   = Axis::model('cms/category_content');
        $mPageContent       = Axis::model('cms/page_content');
        foreach (Axis_Collect_Language::collect() as $langId => $langName) {
            $mPageContent->insert(array(
                'cms_page_id'   => $privacyPageId,
                'language_id'   => $langId,
                'link'          => 'privacy',
                'title'         => 'Privacy policy',
                'content'       => "<div class=\"col2-set\">\n    <div class=\"col-1\">asd, consectetuer adipiscing elit. Sed dolor urna, dapibus ac, convallis eget, ornare a, nisl. Quisque vestibulum congue est. Vivamus ante. Nullam neque tellus, aliquet sed, placerat eget, sagittis hendrerit, leo. Fusce varius pulvinar pede. Fusce at lectus. Nunc ac purus. Aenean rhoncus lacinia nisl. Sed eros sapien, pretium ut, condimentum at, lobortis porta, leo. Aenean ut nibh non metus porttitor sodales. Suspendisse nisl. Phasellus condimentum egestas magna.\nFusce porta porttitor enim. Aenean tempor est nec massa. Phasellus in sapien. Vestibulum urna odio, imperdiet eu, faucibus eu, pretium a, arcu. Morbi mi urna, commodo in, eleifend id, interdum sed, lorem. Pellentesque ullamcorper purus in sapien. Integer faucibus quam a leo. Vivamus posuere porta ipsum. In purus. Proin commodo. Cras eget mi in lacus dignissim volutpat. Fusce orci. Donec eget erat. Mauris sapien libero, sodales non, consequat ut, varius id, lorem.\nMorbi adipiscing. Aenean eu mi. Praesent erat lectus, fringilla non, condimentum ut, semper vitae, lectus. Ut vulputate. Vivamus purus velit, semper nec, dignissim vel, faucibus nec, felis. Cras posuere. Curabitur dignissim convallis lorem. Aenean bibendum auctor justo. Nulla nec diam vel justo rutrum tempor. Suspendisse nec tortor a eros laoreet rutrum. In sapien.\n</div>\n<div class=\"col-2\">\nCras sem sem, condimentum sed, bibendum ac, molestie eget, felis. Integer diam pede, pulvinar quis, eleifend vel, pretium at, nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque id lectus vitae augue imperdiet sodales. Donec dapibus nisl nec arcu. Ut felis turpis, accumsan ac, sagittis a, aliquet vitae, leo. Nullam venenatis, leo quis consectetuer ullamcorper, nisl justo bibendum lorem, sit amet pulvinar quam lorem nec sem. Nunc eleifend, quam sed viverra sollicitudin, dui arcu aliquet nibh, nec tincidunt sem quam non ante. Maecenas et lectus. In tincidunt nisl et velit. Nam venenatis, augue eget congue gravida, mi neque facilisis nunc, sed porta augue erat ac felis\nProin tellus risus, pulvinar ac, pretium eu, faucibus eu, ipsum. Fusce mauris nisl, elementum id, auctor sit amet, porta ac, nibh. Mauris vulputate egestas ipsum.\n\nNunc eget nisi. Phasellus id elit nec elit sollicitudin imperdiet. Cras a justo. Praesent orci. Vivamus sagittis libero ut nulla. Integer dapibus lectus quis lorem. Maecenas consectetuer urna vitae lectus volutpat malesuada. Duis risus. Sed vulputate nulla ac nibh. Pellentesque tincidunt pharetra turpis. Cras libero velit, tristique ut, vehicula a, tempor in, nibh. Praesent ac magna at risus lobortis dictum. Curabitur ultrices neque vehicula neque.</div>\n</div>",
                'meta_description' => 'description of privacy policy page',
                'meta_keyword'  => 'privacy',
                'meta_title'    => 'Privacy policy'
            ));
            $mPageContent->insert(array(
                'cms_page_id'   => $shippingPageId,
                'language_id'   => $langId,
                'link'          => 'shipping',
                'title'         => 'Shipping and returns',
                'content'       => "Praesent vestibulum iaculis eros. Donec porta odio in tortor. Proin nulla nunc, ornare eu, rhoncus non, laoreet quis, sem. Praesent dictum, sapien a fermentum adipiscing, erat mauris dignissim nisi, <span style=\"background-color: rgb(153, 204, 0);\">nec placerat lorem quam id est.<br><br><span style=\"background-color: rgb(192, 192, 192);\">Nullam ut libero. Fusce libero magna, iaculis ac, tempus nec, posuere id, felis. Nulla suscipit augue in sapien. Nullam congue convallis dolor. Cras gravida felis vel nulla. Etiam pulvinar sem in nisi ornare mattis. Proin tempus.</span></span><span style=\"background-color: rgb(192, 192, 192);\"> </span><br><br>Phasellus tincidunt mattis nunc. Fusce lorem. Sed consequat. Nulla ac purus. Donec vel nibh. Aliquam in sapien. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. In eget quam.",
                'meta_description' => '',
                'meta_keyword'  => '',
                'meta_title'    => 'Shipping and Returns'
            ));
            $mPageContent->insert(array(
                'cms_page_id'   => $termsPageId,
                'language_id'   => $langId,
                'link'          => 'terms-of-use',
                'title'         => 'Terms of use',
                'content'       => "{{static_intro}}<br>\n<p>\nIt is a long established fact that a reader will be distracted by the\nreadable content of a page when looking at its layout. The point of\nusing  is that it has a more-or-less normal distribution of\nletters, as opposed to using \"Content here, content here\", making it\nlook like readable English.</p>&nbsp;{{static_google_banner}}\n<br>\n<p>\nMany desktop publishing packages and web\npage editors now use Lorem Ipsum as their default model text, and a\nsearch for \"lorem ipsum\" will uncover many web sites still in their\ninfancy. Various versions have evolved over the years, sometimes by\naccident, sometimes on purpose (injected humour and the like).\n</p>\n{{static_name_new}}",
                'meta_description' => '',
                'meta_keyword'  => '',
                'meta_title'    => 'Terms of Use'
            ));
            $mPageContent->insert(array(
                'cms_page_id'   => $historyPageId,
                'language_id'   => $langId,
                'link'          => 'company-history',
                'title'         => 'Company history',
                'content'       => "<div class=\"col3-set\">\n    <div class=\"col-1\">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed dolor urna, dapibus ac, convallis eget, ornare a, nisl. Quisque vestibulum congue est. Vivamus ante. Nullam neque tellus, aliquet sed, placerat eget, sagittis hendrerit, leo. Fusce varius pulvinar pede. Fusce at lectus. Nunc ac purus. Aenean rhoncus lacinia nisl. Sed eros sapien, pretium ut, condimentum at, lobortis porta, leo. Aenean ut nibh non metus porttitor sodales. Suspendisse nisl. Phasellus condimentum egestas magna.\nFusce porta porttitor enim. Aenean tempor est nec massa. Phasellus in sapien. Vestibulum urna odio, imperdiet eu, faucibus eu, pretium a, arcu. Morbi mi urna, commodo in, eleifend id, interdum sed, lorem. Pellentesque ullamcorper purus in sapien. Integer faucibus quam a leo. Vivamus posuere porta ipsum. In purus. Proin commodo. Cras eget mi in lacus dignissim volutpat. Fusce orci. Donec eget erat. Mauris sapien libero, sodales non, consequat ut, varius id, lorem.\n</div>\n  <div class=\"col-2\">\nMorbi adipiscing. Aenean eu mi. Praesent erat lectus, fringilla non, condimentum ut, semper vitae, lectus. Ut vulputate. Vivamus purus velit, semper nec, dignissim vel, faucibus nec, felis. Cras posuere. Curabitur dignissim convallis lorem. Aenean bibendum auctor justo. Nulla nec diam vel justo rutrum tempor. Suspendisse nec tortor a eros laoreet rutrum. In sapien.\nCras sem sem, condimentum sed, bibendum ac, molestie eget, felis. Integer diam pede, pulvinar quis, eleifend vel, pretium at, nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque id lectus vitae augue imperdiet sodales. Donec dapibus nisl nec arcu. Ut felis turpis, accumsan ac, sagittis a, aliquet vitae, leo. Nullam venenatis, leo quis consectetuer ullamcorper, nisl justo bibendum lorem, sit amet pulvinar quam lorem nec sem. Nunc eleifend, quam sed viverra sollicitudin, dui arcu aliquet nibh, nec tincidunt sem quam non ante. Maecenas et lectus. In tincidunt nisl et velit. Nam venenatis, augue eget congue gravida, mi neque facilisis nunc, sed porta augue erat ac felis. </div>\n<div class=\"col-3\">Proin tellus risus, pulvinar ac, pretium eu, faucibus eu, ipsum. Fusce mauris nisl, elementum id, auctor sit amet, porta ac, nibh. Mauris vulputate egestas ipsum.\n\nNunc eget nisi. Phasellus id elit nec elit sollicitudin imperdiet. Cras a justo. Praesent orci. Vivamus sagittis libero ut nulla. Integer dapibus lectus quis lorem. Maecenas consectetuer urna vitae lectus volutpat malesuada. Duis risus. Sed vulputate nulla ac nibh. Pellentesque tincidunt pharetra turpis. Cras libero velit, tristique ut, vehicula a, tempor in, nibh. Praesent ac magna at risus lobortis dictum. Curabitur ultrices neque vehicula neque.</div>\n</div>",
                'meta_description' => '',
                'meta_keyword'  => 'Company, about, history',
                'meta_title'    => 'Company history'
            ));
            $mPageContent->insert(array(
                'cms_page_id'   => $careersPageId,
                'language_id'   => $langId,
                'link'          => 'careers',
                'title'         => 'Careers at our store',
                'content'       => "<div class=\"col2-set\" style=\"margin-bottom: 7px;\">\n <div class=\"col-1\">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed dolor urna, dapibus ac, convallis eget, ornare a, nisl. Quisque vestibulum congue est. Vivamus ante. Nullam neque tellus, aliquet sed, placerat eget, sagittis hendrerit, leo. Fusce varius pulvinar pede. Fusce at lectus. Nunc ac purus. Aenean rhoncus lacinia nisl. Sed eros sapien, pretium ut, condimentum at, lobortis porta, leo. Aenean ut nibh non metus porttitor sodales. Suspendisse nisl. Phasellus condimentum egestas magna. </div>\n <div class=\"col-2\">Fusce porta porttitor enim. Aenean tempor est nec massa. Phasellus in sapien. Vestibulum urna odio, imperdiet eu, faucibus eu, pretium a, arcu. Morbi mi urna, commodo in, eleifend id, interdum sed, lorem. Pellentesque ullamcorper purus in sapien. Integer faucibus quam a leo. Vivamus posuere porta ipsum. In purus. Proin commodo. Cras eget mi in lacus dignissim volutpat. Fusce orci. Donec eget erat. Mauris sapien libero, sodales non, consequat ut, varius id, lorem. </div>\n</div>\n<div>\nCras sem sem, condimentum sed, bibendum ac, molestie eget, felis. Integer diam pede, pulvinar quis, eleifend vel, pretium at, nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque id lectus vitae augue imperdiet sodales. Donec dapibus nisl nec arcu. Ut felis turpis, accumsan ac, sagittis a, aliquet vitae, leo. Nullam venenatis, leo quis consectetuer ullamcorper, nisl justo bibendum lorem, sit amet pulvinar quam lorem nec sem. Nunc eleifend, quam sed viverra sollicitudin, dui arcu aliquet nibh, nec tincidunt sem quam non ante. Maecenas et lectus. In tincidunt nisl et velit. Nam venenatis, augue eget congue gravida, mi neque facilisis nunc, sed porta augue erat ac felis. Proin tellus risus, pulvinar ac, pretium eu, faucibus eu, ipsum. Fusce mauris nisl, elementum id, auctor sit amet, porta ac, nibh. Mauris vulputate egestas ipsum.\n\nNunc eget nisi. Phasellus id elit nec elit sollicitudin imperdiet. Cras a justo. Praesent orci. Vivamus sagittis libero ut nulla. Integer dapibus lectus quis lorem. Maecenas consectetuer urna vitae lectus volutpat malesuada. Duis risus. Sed vulputate nulla ac nibh. Pellentesque tincidunt pharetra turpis. Cras libero velit, tristique ut, vehicula a, tempor in, nibh. Praesent ac magna at risus lobortis dictum. Curabitur ultrices neque vehicula neque. \n</div>",
                'meta_description' => '',
                'meta_keyword'  => '',
                'meta_title'    => 'Careers at our store'
            ));
            $mCategoryContent->insert(array(
                'cms_category_id'   => $generalCatId,
                'language_id'       => $langId,
                'link'              => 'general'
            ));
            $mCategoryContent->insert(array(
                'cms_category_id'   => $aboutCatId,
                'language_id'       => $langId,
                'link'              => 'about-us'
            ));
        }

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