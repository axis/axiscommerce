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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Core_Upgrade_0_2_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.3';
    protected $_info = 'Box configuration format changed to JSON';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('core_template_box_page')}`
            MODIFY COLUMN `sort_order` TINYINT(3) DEFAULT NULL;

        ");

        $rowset = Axis::model('core/template_box')->fetchAll();
        foreach ($rowset as $row) {
            if (empty($row->config)) {
                $row->config = '{}';
                $row->save();
                continue;
            }

            try {
                if (is_array(Zend_Json::decode($row->config))) {
                    continue;
                }
            } catch (Exception $e) {
                // non-json content
            }

            $config = array();
            foreach (explode(',', $row->config) as $_param) {
                list($key, $value) = explode(':', $_param);
                if ('disable_wrapper' === $key) {
                    $value = (int) $value;
                }
                $config[$key] = $value;
            }

            $row->config = Zend_Json::encode($config);
            $row->save();
        }
    }
}