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

class Axis_Core_Upgrade_0_1_8 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.8';
    protected $_info = 'Session validators added';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('core/session/remoteAddressValidation', 'Core/Session/Remote Address (IP) Validation', 0, 'bool')
            ->add('core/session/httpUserAgentValidation', 'Core/Session/User Agent (Browser) Validation', 0, 'bool');
    }

    public function down()
    {
        Axis::single('core/config_field')->remove('core/session');
    }
}