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
 * @package     Axis_PaymentFreeOrder
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentFreeOrder_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = 'Filters by max and min order totals added';

    public function up()
    {
        $this->getConfigBuilder()
            ->section('payment')
                ->section('FreeOrder_Standard')
                    ->option('minOrderTotal', 'Minimum order total amount')
                        ->setTranslation('Axis_Admin')
                    ->option('maxOrderTotal', 'Maximum order total amount')
                        ->setTranslation('Axis_Admin')

            ->section('/');
    }
}