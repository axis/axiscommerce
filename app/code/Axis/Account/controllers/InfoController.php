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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_InfoController extends Axis_Account_Controller_Account
{
    public function init()
    {
        parent::init();
        $this->view->crumbs()->add(
            Axis::translate('account')->__('Information'), '/account/info'
        );
    }

    public function indexAction()
    {
        $this->_redirect('account/info/change');
    }

    public function changeAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__('Change Info');
        $this->view->meta()->setTitle(
            Axis::translate('account')->__(
                'Change account info'
        ));
        $form = Axis::single('account/Form_ChangeInfo');

        if ($this->_request->isPost()) {
            $data = $this->_request->getPost();
            if ($data['change_password_toggle'] == 0) {
                $form->getElement('password_current')->clearValidators();// removeValidator('NotEmpty');
                $form->getElement('password')->clearValidators(); //removeValidator('NotEmpty');
                $form->getElement('password_confirm')->clearValidators(); //removeValidator('NotEmpty');
            }
            if ($form->isValid($data)) {
                $data['id'] = Axis::getCustomerId();
                Axis::single('account/customer')->save($data);
                Axis::message()->addSuccess(
                    Axis::translate('Axis_Core')->__(
                        'Data was saved successfully'
                ));
            }
        } else {
            $data = array();
            $customer = Axis::single('account/customer')
                ->find(Axis::getCustomerId())->current();
            $extraInfo = $customer->findDependentRowset(
                'Axis_Account_Model_Customer_Detail'
            );
            $data = $customer->toArray();

            foreach ($extraInfo as $row) {
                $data['field_' . $row->customer_field_id] = empty($row->data) ?
                    $row->customer_valueset_value_id : $row->data;
            }
        }

        $form->populate($data);
        $this->view->form = $form;
        $this->render();
    }

}