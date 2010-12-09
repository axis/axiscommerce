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
 * @copyright   Copyright 2008-2010 Axis
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
        $form = Axis::single('contacts/form_message');
        
        if ($this->_request->isPost()) {
            $data = $this->_request->getPost();
            if ($form->isValid($data)) {
                $customInfo = '';
                foreach ($form->getElements() as $element) {
                    if (($element->getValue() != '')
                        && (true != $element->getAttrib('skip'))) {
                        $customInfo .= $element->getLabel()
                            . ': ' . $element->getValue() . "\n";
                    }
                }
                Axis::single('contacts/message')->add(
                    $data['email'], 
                    $data['subject'], 
                    $data['message'], 
                    $data['department'],
                    $customInfo
                );
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
        } elseif ($customerId = Axis::getCustomerId()) {
            $customer = Axis::single('account/customer')
                ->find($customerId)->current();
            $form->getElement('email')->setValue($customer->email);
            $form->getElement('name')->setValue(
                $customer->firstname . ' ' . $customer->lastname
            );
        }
        
        $this->view->pageTitle = Axis::translate('contacts')->__(
            'Contact Us'
        );
        $this->view->form = $form;
        $this->render();
    }
}