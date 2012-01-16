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
 * @package     Axis_PaymentPaypal
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentPaypal_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = 'Unused config options removed';

    public function up()
    {
        Axis::single('core/config_field')
            ->remove('payment/Paypal_Standard/debugUrl')
            ->remove('payment/Paypal_Standard/isDebugMode')
            ->remove('payment/Paypal_Standard/type')
            ->remove('payment/Paypal_Standard/url');
    }
}