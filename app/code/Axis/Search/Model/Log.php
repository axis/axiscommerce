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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Search_Model_Log extends Axis_Db_Table
{
    protected $_name = 'search_log';

    protected $_selectClass = 'Axis_Search_Model_Log_Select';

    /**
     * @param array $data
     * @return mixed
     */
    public function logging(array $data = array())
    {
        $pdata = array(
           'num_results' => $data['num_results'],
           'created_at'  => Axis_Date::now()->toSQLString(),
           'visitor_id'  => Axis::single('log/visitor')->getVisitor()->id,
           'customer_id' => Axis::getCustomerId(),
           'site_id'     => Axis::getSiteId()
        );

        $query = Axis::single('search/log_query')->getQuery($data['query']);
        $query->hit++;
        $query->save();
        $pdata['query_id'] = $query->id;

        /* @todo analize search query */
        return parent::insert($pdata);
    }
}