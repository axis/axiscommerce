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
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Observer
{
    /**
     * Send notification emails to registered cutomer, store owner
     *
     * @param array $data
     * Array(
     *  'customer' => Axis_Account_Model_Customer_Row
     *  'password' => string
     * )
     * @return void
     */
    public function notifyCustomerRegistration($data)
    {
        try {
            $mail = new Axis_Mail();
            $mail->setLocale($data['customer']->locale);
            $configResult = $mail->setConfig(array(
                'event' => 'account_new-customer',
                'subject' => Axis::translate('account')->__(
                    "Welcome, %s %s",
                    $data['customer']->firstname,
                    $data['customer']->lastname
                ),
                'data' => array(
                    'customer' => $data['customer'],
                    'password' => $data['password']
                ),
                'to' => $data['customer']->email
            ));
            $mail->send();
//            if ($configResult) {
//                Axis::message()->addSuccess(
//                    Axis::translate('core')->__('Mail was sended successfully')
//                );
//            }
        } catch (Zend_Mail_Exception $e) {
            Axis::message()->addError(
                Axis::translate('core')->__('Mail sending was failed.')
            );
        }

        try {
            $mailBoxes = Axis::model('core/option_mail_boxes');
            
            $mailNotice = new Axis_Mail();
            $mailNotice->setLocale(Axis::config('locale/main/language_admin'));
            $mailNotice->setConfig(array(
                'event'   => 'account_new-owner',
                'subject' => Axis::translate('account')->__('New Account Created'),
                'data'    => array(
                    'customer' => $data['customer']
                ),
                'to' => $mailBoxes[Axis::config('core/company/administratorEmail')]
            ));
            $mailNotice->send();
        } catch (Zend_Mail_Exception $e) {
        }
    }

    /**
     *
     * @param Axis_Account_Box_Navigation $box
     */
    public function prepareAccountNavigationBox(Axis_Account_Box_Navigation $box)
    {
        $view = $box->getView();
        $box->addItem($view->href('account', true), 'My Account', 'link-account', 10)
            ->addItem($view->href('account/info/change', true), 'Change Info', 'link-change-info', 20)
            ->addItem($view->href('account/address-book', true), 'Address Book', 'link-address-book', 30)
            ->addItem($view->href('account/order', true), 'My Orders', 'link-orders', 40)
            ->addItem($view->href('account/wishlist', true), 'My Wishlist', 'link-wishlist', 50)
            ->addItem($view->href('account/auth/logout', true), 'Logout', 'link-logout', 100);
    }

    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'customer' => array(
                'label'         => 'Customers',
                'order'         => 60,
                'uri'           => '#',
                'translator'    => 'Axis_Account',
                'pages'         => array(
                    'customer/index' => array(
                        'label'         => 'Manage Customers',
                        'order'         => 10,
                        'module'        => 'Axis_Account',
                        'controller'    => 'customer',
                        'action'        => 'index',
                        'route'         => 'admin/axis/account',
                        'resource'      => 'admin/axis/account/customer/index'
                    ),
                    'customer/group' => array(
                        'label'         => 'Customer Groups',
                        'order'         => 20,
                        'module'        => 'Axis_Account',
                        'controller'    => 'group',
                        'action'        => 'index',
                        'route'         => 'admin/axis/account',
                        'resource'      => 'admin/axis/account/group/index'
                    ),
                    'customer/wishlist' => array(
                        'label'         => 'Wishlist',
                        'order'         => 30,
                        'module'        => 'Axis_Account',
                        'controller'    => 'wishlist',
                        'action'        => 'index',
                        'route'         => 'admin/axis/account',
                        'resource'      => 'admin/axis/account/wishlist/index'
                    ),
                    'customer/field' => array(
                        'label'         => 'Customer Info Fields',
                        'order'         => 40,
                        'module'        => 'Axis_Account',
                        'controller'    => 'field',
                        'action'        => 'index',
                        'route'         => 'admin/axis/account',
                        'resource'      => 'admin/axis/account/field/index'
                    )
                )
            )
        ));
    }

    /**
     * Register new customer, fill the customer_id in order
     * and save the customer addresses
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return void
     */
    public function saveCustomerAfterPlaceOrder(Axis_Sales_Model_Order_Row $order)
    {
        $checkout = Axis::single('checkout/checkout');
        $billing  = $checkout->getBilling()->toFlatArray();
        $delivery = $checkout->getDelivery()->toFlatArray();

        $newCustomer = false;
        if (!empty($billing['register']) && !Axis::getCustomerId()) {
            $modelCustomer = Axis::model('account/customer');
            $userData = $billing;
            $userData['site_id'] = Axis::getSiteId();
            $userData['is_active'] = 1;
            unset($userData['id']);

            list($customer, $password) = $modelCustomer->create($userData);
            $customer->setDetails($userData);
            $modelCustomer->login($userData['email'], $password);
            $newCustomer = true;

            $order->customer_id = $customer->id;
            $order->save();
        }

        // save address if needed
        if ($customer = Axis::getCustomer()) {
            if (empty($billing['id'])) {
                $customer->setAddress($billing);
            }
            if (empty($delivery['id']) && empty($billing['use_for_delivery'])) {
                $customer->setAddress($delivery);
            }
        }

        if ($newCustomer) {
            Axis::dispatch('account_customer_register_success', array(
                'customer' => $customer,
                'password' => $password
            ));
        }
    }
}
