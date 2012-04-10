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
            'shipping/Fedex_Standard/package'      => 'shippingFedex/option_standard_package',
            'shipping/Fedex_Standard/dropoff'      => 'shippingFedex/option_standard_pickup',
            'shipping/Fedex_Standard/allowedTypes' => 'shippingFedex/option_standard_service',
            'shipping/Fedex_Standard/measure'      => 'shippingFedex/option_standard_measure'
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($paths[$row->path])) {
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
    }
}
