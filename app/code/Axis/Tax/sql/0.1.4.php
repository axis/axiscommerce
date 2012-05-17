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
 * @package     Axis_Tax
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Tax_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = 'Model names added to the total configuration';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('orderTotal', 'Order Total Modules', null, null, array('translation_module' => 'Axis_Checkout'))
            ->add('orderTotal/tax/model', 'Order Total Modules/Tax/Model', 'tax/checkout_total_tax')
            ->add('orderTotal/shipping_tax/model', 'Order Total Modules/ShippingTax/Model', 'tax/checkout_total_shippingTax');
    }
}
