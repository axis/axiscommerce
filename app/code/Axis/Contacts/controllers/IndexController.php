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
 * @subpackage  Axis_Contacts_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Contacts_IndexController extends Axis_Core_Controller_Front
{

    public function indexAction()
    {
        $this->setTitle(
            Axis::translate('contacts')->__(
                'Contact Us'
        ));
        $form = Axis::model('contacts/form_message');

        if ($this->_request->isPost()) {
            $data = $this->_request->getPost();
            if ($form->isValid($data)) {
                $custom = array();
                foreach ($form->getElements() as $element) {
                    $elementsToSkip = array(
                        'email',
                        'subject',
                        'department_id',
                        'message',
                        'submit'
                    );
                    if (in_array($element->getName(), $elementsToSkip)) {
                        continue;
                    }
                    $custom[$element->getLabel()] = $element->getValue();
                }
                $data['custom_info'] = Zend_Json::encode($custom);
                $data['site_id']     = Axis::getSiteId();

                Axis::model('contacts/message')->save($data);

                $department = Axis::single('contacts/department')
                    ->find($data['department_id'])
                    ->current();

                if ($department) {
                    try {
                        $mail = new Axis_Mail();
                        $mail->setLocale(Axis::config('locale/main/language_admin'));
                        $mail->setConfig(array(
                            'event'   => 'contact_us',
                            'subject' => $data['subject'],
                            'data'    => $data,
                            'to'      => $department->email,
                            'from'    => array(
                                'name'  => $data['name'],
                                'email' => $data['email']
                            )
                        ));
                        $mail->send();
                    } catch (Zend_Mail_Exception $e) {
                    }
                }

                Axis::message()->addSuccess(
                    Axis::translate('contacts')->__(
                        'Your message was successfully added'
                    )
                );
                $this->_redirect(
                    $this->getRequest()->getServer('HTTP_REFERER')
                );
            } else {
                $form->populate($data);
            }
        } elseif ($customer = Axis::getCustomer()) {
            $form->getElement('email')->setValue($customer->email);
            $form->getElement('name')->setValue(
                $customer->firstname . ' ' . $customer->lastname
            );
        }

        $this->view->form = $form;
        $this->render();
    }
}