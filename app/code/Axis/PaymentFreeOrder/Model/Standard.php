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
 * @subpackage  Axis_PaymentFreeOrder_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_PaymentFreeOrder
 * @subpackage  Axis_PaymentFreeOrder_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_PaymentFreeOrder_Model_Standard extends Axis_Method_Payment_Model_Abstract
{
    protected $_code = 'FreeOrder_Standard';
    protected $_title = 'The Axis FREE CHARGE CARD';
    protected $_description = 'Used for purchase of FREE CHARGE ONLY';

    public function isAllowed($request)
    {
        if (intval($request['price']) != 0) {
            return false;
        }
        return parent::isAllowed($request);
    }
}
