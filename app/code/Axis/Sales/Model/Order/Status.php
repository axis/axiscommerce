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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Status extends Axis_Db_Table
{
    protected $_name = "sales_order_status";
    protected $_rowClass = 'Axis_Sales_Model_Order_Status_Row';
    protected $_primary = array('id');

    /**
     * Retrieve an array of order statuses
     * @param int parentId
     * @return array
     */
    public function getList($statusId = null)
    {
        $select = $this->select('*')
            ->joinLeft('sales_order_status_text',
                'sos.id = sost.status_id',
                array('language_id', 'status_name')
            );

        $childrens = array();
        if (null !== $statusId) {
            $childrens = Axis::single('sales/order_status_relation')->getChildrens($statusId);
            if (count($childrens)) {
                $select->where('sos.id IN (?)', $childrens);
            } else {
                return array();
            }
        }

        return $select->fetchAll();
    }

    /**
     *
     * @param string $name
     * @param int|string $from
     * @param array|int|string $to
     * @param array of string $translates ( 1 => 'pending', 2 => 'jxsredfyyz')
     * @return mixed false|$this Provides fluent interface
     */
    public function add($name, $from = array(), $to = array(), $translates = array())
    {
        if ($this->getIdByName($name)) {
            Axis::message()->addError(
                Axis::translate('sales')->__(
                    'Order status "%s" already exist', $name
            ));
            return false;
        }

        $row = $this->createRow(array(
            'name' => $name,
            'system' => 0
        ));
        $row->save();

        //add relation
        $modelRealation = Axis::model('sales/order_status_relation');
        if (is_string($from)) {
            $from = array($this->getIdByName($from));
        }
        $modelRealation->add($from, $row->id);

        if (is_string(to)) {
            $to = array($this->getIdByName($to));
        }
        if (!is_array($to)) {
            $to = array($to);
        }
        foreach ($to as $_to) {
            $modelRealation->add($row->id, $_to);
        }

        //add labels
        $modelLabel  = Axis::model('sales/order_status_text');
        $languageIds = array_keys(Axis_Locale_Model_Option_Language::getConfigOptionsArray());
        foreach ($languageIds as $languageId) {
            if (!isset($translates[$languageId])) {
                continue;
            }
            $modelLabel->createRow(array(
                'status_id'   => $row->id,
                'language_id' => $languageId,
                'status_name' => $translates[$languageId]
            ))->save();
        }

        Axis::message()->addSuccess(
            Axis::translate('sales')->__(
                "New order status create : %s", $name
        ));

        return $this;
    }
}