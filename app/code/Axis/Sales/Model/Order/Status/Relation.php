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
class Axis_Sales_Model_Order_Status_Relation extends Axis_Db_Table
{
    protected $_name = 'sales_order_status_relation';


    /**
     * Return parent(s) statuses
     * @param int $statusId
     * @return array
     */
    public function getParents($statusId)
    {
        return $this->select('from_status')
            ->where('to_status = ?', $statusId)
            ->fetchCol();
    }

    /**
     * Return child statuses
     * @param int $statusId
     * @return array
     */
    public function getChildrens($statusId)
    {
        return $this->select('to_status')
            ->where('from_status = ?', $statusId)
            ->fetchCol();
    }

    /**
     *  Add order status relation
     * @param int|string from
     * @param int|string to
     * @return $this Fluent interface
     */
    public function add($from, $to)
    {
        $model = Axis::model('sales/order_status');
        if (!is_numeric($from)) {
            $_from = $model->getIdByName($from);
            if (null !== $_from) {
                $from = $_from;
            }
        }
        if (!is_numeric($to)) {
            $_to = $model->getIdByName($to);
            if (null !== $_to) {
                $to = $_to;
            }
        }

        $row = $this->createRow(array(
            'from_status' => $from,
            'to_status'   => $to,
        ));
        $row->save();

        return $this;
    }

    /**
     *  Remove order status relation
     * @param int|string from
     * @param int|string to
     * @return $this Fluent interface
     */
    public function remove($from, $to)
    {
        if (is_string($from)) {
            $from = Axis::single('sales/order_status')->getIdByName($from);
        }

        if (is_string($to)) {
            $to = Axis::single('sales/order_status')->getIdByName($to);
        }
        $this->delete($this->getAdapter()->quoteInto(
            "from_status = {$from} AND to_status =  {$to}"
        ));
        return $this;
    }

}