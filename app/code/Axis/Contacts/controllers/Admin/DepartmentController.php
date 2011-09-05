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
class Axis_Contacts_Admin_DepartmentController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $rowset = Axis::single('contacts/department')->fetchAll();

        $data = array();
        foreach ($rowset as $row) {
            $data[] = array(
                'text' => $row->name,
                'id'   => $row->id,
                'leaf' => true
            );
        }

        return $this->_helper->json
            ->sendRaw($data);
    }

    public function loadAction()
    {
        $data = array();
        $id = $this->_getParam('id');
        $row = Axis::single('contacts/department')->find($id)->current();
            
        if ($row) {
            $data = $row->toArray();
        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
    
    public function saveAction()
    {
        $_row = array(
            'name'  => $this->_getParam('name'),
            'email' => $this->_getParam('email')
        );
        $id = $this->_getParam('id');
        $model = Axis::single('contacts/department');
        $row = $model->find($id)->current();
        if (!$row) {
            $row = $model->createRow();
        }
        $row->setFromArray($_row)->save();
        Axis::message()->addSuccess(
            Axis::translate('contacts')->__(
               'Department was saved succesfully'
            )
        );

        return $this->_helper->json->sendSuccess();
    }
    
    public function removeAction()
    {
        $id = $this->_getParam('id', 0);

        if (!$id) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $model = Axis::single('contacts/department');

        $model->delete($this->db->quoteInto('id = ?', $id));
        Axis::message()->addSuccess(
            Axis::translate('contacts')->__(
                'Department was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}
