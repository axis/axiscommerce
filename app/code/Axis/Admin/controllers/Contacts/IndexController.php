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
 * @copyright   Copyright 2008-2010 Axis
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
            ->addDepartamentFilter($this->_getParam('depId', 0))
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

        $data = array(
            'id' => $this->_getParam('id', 0),
            'name'  => $this->_getParam('name'),
            'email' => $this->_getParam('email')
        );

        Axis::single('contacts/department')->save($data);

        $this->_helper->json->sendSuccess();
    }

    public function getDepartmentsAction()
    {
        $this->_helper->layout->disableLayout();

        $items = Axis::single('contacts/department')->fetchAll();

        $result = array();
        foreach ($items as $item) {
            $result[] = array(
                'text' => $item->name,
                'id'   => $item->id,
                'leaf' => true
            );
        }

        $this->_helper->json->sendRaw($result);
    }

    public function getDepartmentAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->_getParam('id', 0);

        $result = array();
        if ($id) {
            $row = Axis::single('contacts/department')
                ->fetchRow($this->db->quoteInto('id = ?', $id));
            $result = $row->toArray();
        }

        $this->_helper->json->sendSuccess(array(
            'data' => array($result)
        ));
    }

    public function sendAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_getAllParams();

        $from = Axis::model('contacts/department')
            ->find($data['depId'])
            ->current()
            ->email;

        $customer = Axis::model('account/customer')->fetchRow(
            Axis::db()->quoteInto('email = ?', $data['email'])
        );

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
        $row = Axis::single('contacts/message')->fetchRow(
            $this->db->quoteInto('id = ?', $this->_getParam('id'))
        );
        $row->message_status = $this->_getParam('message_status');

        $this->_helper->json->sendJson(array(
            'success' => $row->save()
        ));
    }
}
