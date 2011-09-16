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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Admin_ValueSetController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $dataset = Axis::single('account/customer_valueSet')->select()
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            )
            ->fetchAll();

        return $this->_helper->json
            ->setData($dataset)
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $dataset    = Zend_Json::decode($this->_getParam('data'));
        $model      = Axis::model('account/customer_valueSet');
        foreach ($dataset as $data) {
            $row = $model->getRow($data);
            $row->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::model('account/customer_valueSet')
            ->delete($this->db->quoteInto('id IN (?)', $data));
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Group was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}