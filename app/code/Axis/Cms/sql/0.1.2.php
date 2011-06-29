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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Cms_Upgrade_0_1_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.2';
    protected $_info = 'Multilanguage in bocks';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $mBlock = Axis::model('cms/block');
        $blockRows = $mBlock->fetchAll();

        $installer->run("

        ALTER TABLE `{$installer->getTable('cms_block')}` DROP COLUMN `content`;

        -- DROP TABLE IF EXISTS `{$installer->getTable('cms_block_content')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('cms_block_content')}` (
          `block_id` int(10) unsigned NOT NULL auto_increment,
          `language_id` smallint(5) unsigned NOT NULL,
          `content` text,
          PRIMARY KEY  USING BTREE (`language_id`,`block_id`),
          KEY `FK_CMS_BLOCK_CONTENT_BLOCK_ID` (`block_id`),
          CONSTRAINT `FK_CMS_BLOCK_CONTENT_LANGUAGE` FOREIGN KEY (`language_id`)
            REFERENCES `{$installer->getTable('locale_language')}` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_CMS_BLOCK_CONTENT_BLOCK_ID` FOREIGN KEY (`block_id`)
            REFERENCES `{$installer->getTable('cms_block')}` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        ");

        $mBlockContent = Axis::model('cms/block_content');
        $languages = Axis::model('locale/language')->fetchAll();
        foreach ($blockRows as $row) {
            foreach ($languages as $language) {
                $mBlockContent->insert(array(
                    'block_id'      => $row->id,
                    'language_id'   => $language['id'],
                    'content'       => $row->content
                ));
            }
        }
    }

    public function down()
    {

    }
}