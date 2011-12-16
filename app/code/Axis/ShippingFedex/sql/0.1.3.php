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
 * @package     Axis_ShippingFedex
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingFedex_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = '';

    public function up()
    {
        $paths = array(
            'shipping/Fedex_Standard/package'      => 'Axis_ShippingFedex_Model_Standard_Package',
            'shipping/Fedex_Standard/dropoff'      => 'Axis_ShippingFedex_Model_Standard_Pickup',
            'shipping/Fedex_Standard/allowedTypes' => 'Axis_ShippingFedex_Model_Standard_Service',
            'shipping/Fedex_Standard/measure'      => 'Axis_ShippingFedex_Model_Standard_Measure'
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($paths[$row->path])) {
                $row->config_options = null; 
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
    }
}
