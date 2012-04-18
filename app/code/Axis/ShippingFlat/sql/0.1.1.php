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
 * @package     Axis_ShippingFlat
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingFlat_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';

    public function up()
    {
        $row = Axis::single('core/config_field')->select()
            ->where('path = ?', 'shipping/Flat_Standard/type')
            ->fetchRow();
        $row->model = 'shippingTable/option_standard_service';
        $row->save();
        
        $rowset = Axis::single('core/config_field')->select()
            ->where('model = ?', 'ShippingFlatRateMultiPrice')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->type = 'shippingflat/standard/multiprice.phtml'; 
            $row->model = 'shippingFlat/option_standard_multiPrice';
            $row->save();
        }
    }
}