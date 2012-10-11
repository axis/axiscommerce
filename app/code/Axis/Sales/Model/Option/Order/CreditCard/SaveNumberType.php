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
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Option_Order_CreditCard_SaveNumberType extends Axis_Config_Option_Array_Abstract
{
    /**
     *
     * @var const array
     */
    protected $_actions = array(
        'dont_save'       => 'Don\'t save',
        'last_four'       => 'Last 4 digits',
        'first_last_four' => 'First and last 4 digits',
        'partial_email'   => 'First and last 4 digits to database, rest send by email',
        'complete'        => 'Save complete number',
    );

    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return $this->_actions;
    }
}