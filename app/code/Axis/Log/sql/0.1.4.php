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
 * @package     Axis_Log
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Log_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = 'unnecessary columns remove';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("
            
            ALTER TABLE `{$installer->getTable('log_visitor')}` 
                DROP COLUMN `last_url_id`,
                DROP COLUMN `last_visit_at`,
                DROP COLUMN `site_id`;
            
            ALTER TABLE `{$installer->getTable('log_visitor_info')}` 
                DROP COLUMN `http_refer`;

        ");
    }
}