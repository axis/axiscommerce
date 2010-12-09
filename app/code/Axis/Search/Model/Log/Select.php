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
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Search_Model_Log_Select extends Axis_Db_Table_Select
{
    /**
     * Adds customer_email column to select
     *
     * @return Axis_Search_Model_Log_Select
     */
    public function addCustomer()
    {
        return $this->joinLeft(
            'account_customer',
            'sl.customer_id = ac.id',
            array(
                'customer_email'    => 'email'
            )
        );
    }

    /**
     * Adds query string and hit count to select
     *
     * @return Axis_Search_Model_Log_Select
     */
    public function addQuery()
    {
        return $this->joinLeft(
            'search_log_query',
            'sl.query_id = slq.id',
            array('query', 'hit')
        );
    }
}