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
 * @package     Axis_Sitemap
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Sitemap_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = 'old config option was removed; table renamed';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("
            ALTER TABLE `{$installer->getTable('sitemap_file')}`
                ADD COLUMN `crawlers` VARCHAR(32) NOT NULL AFTER `usage_at`;

            ALTER TABLE `{$installer->getTable('sitemap_file')}`
                CHANGE COLUMN `generated_at` `created_on` DATETIME  NOT NULL;

            ALTER TABLE `{$installer->getTable('sitemap_file')}`
                CHANGE COLUMN `usage_at` `modified_on` DATETIME  NOT NULL;

            ALTER TABLE `{$installer->getTable('sitemap_file')}`
                MODIFY COLUMN `filename` VARCHAR(225) NOT NULL;

            ALTER TABLE `{$installer->getTable('sitemap_file')}`
                RENAME TO `{$installer->getTable('sitemap')}`;

            DROP TABLE IF EXISTS `{$installer->getTable('sitemap_file_engine')}`;
        ");

        Axis::single('core/config_builder')
            ->remove('sitemap/main/startTime')
            ->remove('sitemap/main/googlePingUrl')
            ->remove('sitemap/main/yahooPingUrl')
            ->remove('sitemap/main/askPingUrl')
            ->remove('sitemap/main/msnPingUrl')
            ;
    }
}