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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Select extends Axis_Admin_Model_Select_Grid
{
    /**
     *
     * @param int $siteId
     * @return Axis_Sales_Model_Order_Select
     */
    public function addSiteFilter($siteId)
    {
        if (!empty($siteId)) {
            $this->where('site_id = ?', $siteId);
        }
        return $this;
    }

    /**
     *
     * @param bool $notGuest
     * @return Axis_Sales_Model_Order_Select
     */
    public function addGuestFilter($notGuest = true)
    {
        if (true === $notGuest) {
            $this->where('customer_id IS NOT NULL');
        } else {
            $this->where('customer_id IS NULL');
        }
        return $this;
    }
}