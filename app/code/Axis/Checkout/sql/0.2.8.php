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
 * @package     Axis_Checkout
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Checkout_Upgrade_0_2_8 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.8';
    protected $_info = 'Model names added to the total configuration';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('orderTotal', 'Order Total Modules', null, null, array('translation_module' => 'Axis_Checkout'))
            ->add('orderTotal/subtotal/model', 'Order Total Modules/Subtotal/Model', 'checkout/total_subtotal')
            ->add('orderTotal/shipping/model', 'Order Total Modules/Shipping/Model', 'checkout/total_shipping');
    }
}
