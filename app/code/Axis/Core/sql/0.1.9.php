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
 * @package     Axis_Core
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Core_Upgrade_0_1_9 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.9';
    protected $_info = 'Rename table core_template_layout_page to core_template_page';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

            ALTER TABLE `{$installer->getTable('core_template_layout_page')}`
                RENAME TO `{$installer->getTable('core_template_page')}`;

            ALTER TABLE `{$installer->getTable('core_template_page')}`
                ADD COLUMN `parent_page_id` MEDIUMINT(8) UNSIGNED AFTER `layout`;

        ");

    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

            ALTER TABLE `{$installer->getTable('core_template_page')}`
                RENAME TO `{$installer->getTable('core_template_layout_page')}`;

            ALTER TABLE `{$installer->getTable('core_template_page')}`
                DROP COLUMN `parent_page_id`;

        ");
    }
}