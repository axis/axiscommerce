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

class Axis_Core_Upgrade_0_2_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.2';
    protected $_info = '';

    public function up()
    {
        $rowset = Axis::single('core/template_box')->fetchAll();
        foreach ($rowset as $row) {
            if (empty($row->config)) {
                continue;
            }
            $config = '';
            foreach (explode(',', $row->config) as $_param) {
                list($key, $value) = explode(':', $_param);
                $key = strtolower(preg_replace( //underscore
                    array('/(.)([A-Z])/', '/(.)(\d+)/'), "$1_$2", $key
                ));
                $config .= "{$key}:{$value},";
            }
            
            $row->config = trim($config, ',');
            $row->save();
        }
    }

    public function down()
    {
        
    }
}