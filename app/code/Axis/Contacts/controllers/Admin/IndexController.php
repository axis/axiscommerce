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
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Contacts_Admin_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('contacts')->__('Incoming Box');
        $this->render();
    }

    public function listAction()
    {
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

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess();
    }

    public function saveAction()
    {
        $id     = $this->_getParam('id');
        $status = $this->_getParam('message_status');

        $row = Axis::single('contacts/message')->find($id)->current();
        $row->message_status = $status;
        $row->save();

        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        if (!count($data)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        Axis::single('contacts/message')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function sendAction()
    {
        $data = $this->_getAllParams();

        $from = Axis::model('contacts/department')
            ->find($data['department_id'])
            ->current()
            ->email;

        $row = Axis::model('account/customer')->select()
            ->where('email = ?', $data['email'])
            ->fetchRow();

        //@todo if null need firstname = full name from custom_info fields
        $firstname = $row ? $row->firstname : null;
        $lastname  = $row ? $row->lastname : null;

        try {
            $mail = new Axis_Mail();
            if ($row) {
                $mail->setLocale($row->locale);
            }
            $configResult = $mail->setConfig(array(
                'event'   => 'default',
                'subject' => $data['subject'],
                'data'    => array(
                    'text'      => $data['message'],
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'customer'  => $row
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
        } catch (Zend_Mail_Exception $e) {
            Axis::message()->addError(
                Axis::translate('core')->__('Mail sending was failed.')
                . ' ' . $e->getMessage()
            );
            return $this->_helper->json->sendFailure();
        }
    }
}
