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
 * @package     Axis_PaymentAuthorizenetAim
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentAuthorizenetAim_Upgrade_0_1_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.2';

    public function up()
    {
        $paths = array(
            'payment/AuthorizenetAim_Standard/orderStatusId'     => 'Axis_Sales_Model_Option_Order_Status',
            'payment/AuthorizenetAim_Standard/authorizationType' => 'paymentAuthorizenetAim/option_standard_authorizationType'
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($paths[$row->path])) {
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
        
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'payment/AuthorizenetAim_Standard/authorizationType')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->value = Axis_PaymentAuthorizenetAim_Model_Option_Standard_AuthorizationType::AUTHORIZE;
            $row->save();
        }
        
    }
}