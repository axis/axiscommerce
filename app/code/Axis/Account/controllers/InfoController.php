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
 * @subpackage  Axis_Account_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_InfoController extends Axis_Account_Controller_Abstract
{
    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_redirect('account/info/change');
    }

    public function changeAction()
    {
        $this->setTitle(Axis::translate('account')->__('Change Info'));
        $form = Axis::single('account/Form_ChangeInfo');

        if ($this->_request->isPost()) {
            $data = $this->_request->getPost();
            if ($data['change_password_toggle'] == 0) {
                $form->getElement('password_current')->clearValidators();// removeValidator('NotEmpty');
                $form->getElement('password')->clearValidators(); //removeValidator('NotEmpty');
                $form->getElement('password_confirm')->clearValidators(); //removeValidator('NotEmpty');
            }
            if ($form->isValid($data)) {

                $model = Axis::single('account/customer');

                $row = $model->find(Axis::getCustomerId())->current();
                if (empty($data['password'])) {
                    unset($data['password']);
                } else {
                    $data['password'] = md5($data['password']);
                }
                $row->setFromArray($data);
                $row->modified_at = Axis_Date::now()->toSQLString();
                $row->save();
                $row->setDetails($data);

                Axis::message()->addSuccess(
                    Axis::translate('Axis_Core')->__(
                        'Data was saved successfully'
                ));
            }
        } else {
            $data = array();
            $customer = Axis::getCustomer();
            $extraInfo = $customer->findDependentRowset(
                'Axis_Account_Model_Customer_Detail'
            );
            $data = $customer->toArray();

            foreach ($extraInfo as $row) {
                $value  = empty($row->data) ? $row->customer_valueset_value_id : $row->data;

                $isMulti = isset($data['field_' . $row->customer_field_id]);

                if ($isMulti && is_array($data['field_' . $row->customer_field_id])) {
                    $data['field_' . $row->customer_field_id][] = $value;
                } elseif ($isMulti) {
                    $data['field_' . $row->customer_field_id] = array(
                        $data['field_' . $row->customer_field_id],
                        $value
                    );

                } else {
                    $data['field_' . $row->customer_field_id] = $value;
                }
            }
        }
        $form->populate($data);
        $this->view->form = $form;
        $this->render();
    }

}