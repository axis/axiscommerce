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
class Axis_Admin_Contacts_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('contacts')->__('Contact Us');
        $this->render();
    }

    public function listAction()
    {
        $this->_helper->layout->disableLayout();

        $select = Axis::single('contacts/message')->select('*')
            ->calcFoundRows()
            ->addDepartamentFilter($this->_getParam('departmentId', 0))
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 25),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        return $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
        ));
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();

        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));

        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        Axis::single('contacts/message')->delete(
            $this->db->quoteInto('id IN(?)', $ids)
        );
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }

    
    public function sendAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_getAllParams();

        $from = Axis::model('contacts/department')
            ->find($data['department_id'])
            ->current()
            ->email;

        $customer = Axis::model('account/customer')->select()
            ->where('email = ?', $data['email'])
            ->fetchRow();

        //@todo if null need firstname = full name from custom_info fields
        $firstname = $customer ? $customer->firstname : null;
        $lastname  = $customer ? $customer->lastname : null;

        try {
            $mail = new Axis_Mail();
            if ($customer) {
                $mail->setLocale($customer->locale);
            }
            $configResult = $mail->setConfig(array(
                'event'   => 'default',
                'subject' => $data['subject'],
                'data'    => array(
                    'text'      => $data['message'],
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'customer'  => $customer
                ),
                'to'      => $data['email'],
                'from'    => array(
                    'email' => $from
                )
            ));
            $mail->send();

            if ($configResult) {
                Axis::message()->addSuccess(
                    Axis::translate('core')->__('Mail was sended successfully')
                );
            }
            $this->_helper->json->sendSuccess();
        } catch (Zend_Mail_Transport_Exception $e) {
            Axis::message()->addError(
                Axis::translate('core')->__('Mail sending was failed.')
                . ' ' . $e->getMessage()
            );
            $this->_helper->json->sendFailure();
        }
    }

    public function setStatusAction()
    {
        $this->_helper->layout->disableLayout();
        
        $id     = $this->_getParam('id');
        $status = $this->_getParam('message_status');
        
        $row = Axis::single('contacts/message')->find($id)->current();
        $row->message_status = $status;
        $row->save();

        $this->_helper->json->sendSuccess();
    }

    public function deleteDepartmentAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->_getParam('id', 0);

        if (!$id) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $departmentModel = Axis::single('contacts/department');

        $departmentModel->delete($this->db->quoteInto('id = ?', $id));
        Axis::message()->addSuccess(
            Axis::translate('contacts')->__(
                'Department was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }

    public function saveDepartmentAction()
    {
        $this->_helper->layout->disableLayout();

        $rowData = array(
            'name'  => $this->_getParam('name'),
            'email' => $this->_getParam('email')
        );
        $id = $this->_getParam('id');
        $model = Axis::single('contacts/department');
        $row = $model->find($id)->current();
        if (!$row) {
            $row = $model->createRow();
        }
        $row->setFromArray($rowData)->save();
        Axis::message()->addSuccess(
            Axis::translate('contacts')->__(
               'Department was saved succesfully'
            )
        );

        $this->_helper->json->sendSuccess();
    }

    public function getDepartmentsAction()
    {
        $this->_helper->layout->disableLayout();

        $rowset = Axis::single('contacts/department')->fetchAll();

        $data = array();
        foreach ($rowset as $row) {
            $data[] = array(
                'text' => $row->name,
                'id'   => $row->id,
                'leaf' => true
            );
        }

        $this->_helper->json->sendRaw($data);
    }

    public function getDepartmentAction()
    {
        $this->_helper->layout->disableLayout();

        $data = array();
        $id = $this->_getParam('id');
        $row = Axis::single('contacts/department')->find($id)->current();
            
        if ($row) {
            $data = $row->toArray();
        }

        $this->_helper->json->setData($data)
            ->sendSuccess();
    }
}
