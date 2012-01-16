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
class Axis_Sales_Model_Order_Select extends Axis_Db_Table_Select
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

    /**
     * Rewriting of parent method.
     * Added support for customer_name and order_total in customer currency filtering
     *
     * @param array $filters
     * <pre>
     *  array(
     *      0 => array(
     *          field       => table_column
     *          value       => column_value
     *          operator    => =|>|<|IN|LIKE    [optional]
     *          table       => table_correlation[optional]
     *      )
     *  )
     * </pre>
     * @return Axis_Sales_Model_Order_Select
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $key => $filter) {
            if ('customer_name' == $filter['field']) {
                $this->where("CONCAT(firstname, ' ', lastname) LIKE ?", '%' . $filter['value'] . '%');
                unset($filters[$key]);
            } else if ('order_total_customer' == $filter['field']) {
                $this->having("{$filter['field']} {$filter['operator']} ?", $filter['value']);
                unset($filters[$key]);
            }
        }

        return parent::addFilters($filters);
    }
}