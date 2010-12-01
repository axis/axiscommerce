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
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Row extends Axis_Db_Table_Row
{
    /**
     * Set order status & insert information to Status History
     *
     * @param integer|string $statusId
     * @param string $comments
     * @param bool $notifyCustomer
     * @return bool
     */
    public function setStatus($statusId, $comments = '', $notifyCustomer = false)
    {
        if (is_string($statusId)
            && in_array($statusId, array_values(Axis_Collect_OrderStatus::collect()))) {

            $status = $statusId;
            $statusId = Axis::single('sales/order_status')->getIdByName($status);
        } else {
            $status = Axis_Collect_OrderStatus::getName($statusId);
        }

        /* Relation exist or not */
        $childrens = Axis::single('sales/order_status_relation')
            ->getChildrens($this->order_status_id);
        if (!in_array($statusId, $childrens)) {
            Axis::message()->addError(
                Axis::translate('sales')->__(
                    'Relation not exist %s <=> %s',
                    $this->order_status_id,
                    $statusId
            ));
            return false;
        }

        $retMethod = Axis::single('sales/order_status_run')->$status($this);

        if (!$retMethod) {
            $status = 'failed';
            Axis::single('sales/order_status_run')->failed($this);
            $statusId = Axis::single('sales/order_status')->getIdByName('failed');
            $notifyCustomer = false;
            $messages = array();
            foreach (Axis::message()->get() as $messageGroup) {
                $comments .= ' ' . implode('.', $messageGroup);
            }
        }

        $this->order_status_id = $statusId;
        $this->save();

        /* save Status History */
        $this->addComment($comments, $notifyCustomer, $statusId);

        $message = Axis::translate('sales')->__(
            "Order status was changed to %s", $status
        );
        if ($status == 'failed' && Zend_Registry::get('area') == 'front') {
            Axis::message()->addError($message);
            if (!$retMethod) {
                Axis::message()->addError($comments);
            }
        } else {
            Axis::message()->addSuccess($message);
        }
        return $retMethod;
    }

    /**
     * Add comment to order(not change order status)
     * Fluent interface
     * @param $comment string
     * @param $notify bool
     * @param $statusId int
     * @return this
     */
    public function addComment($comment, $notify = false, $statusId = null)
    {
        if (null === $statusId) {
            $statusId = $this->order_status_id;
        }
        if ($notify) {
            $notify = $this->_notify($comment);
        }
        Axis::single('sales/order_status_history')->insert(array(
            'order_id' => $this->id,
            'order_status_id' => $statusId,
            'created_on' => Axis_Date::now()->toSQLString(),
            'notified' => (int) $notify,
            'comments' => $comment
        ));

        return $this;
    }

    /**
     * Notify customer
     * @return unknown_type
     */
    protected function _notify($comments)
    {
        $order      = Axis::single('sales/order')->find($this->id)->current();
        $customerId = Axis::single('sales/order')->getCustomerId($this->id);
        $customer   = Axis::single('account/customer')->find($customerId)->current();
        $status     = Axis::single('sales/order_status_text')->find(
                $this->order_status_id,
                Axis_Locale::getLanguageId()
            )->current()
            ->toArray();
        $status = $status['status_name'];
        $to = $customer->toArray();
        $to = $to['email'];

        $mail = new Axis_Mail();

        $mail->setConfig(array(
            'event'   => 'change_order_status-customer',
            'subject' => 'Your order change status notify',
            'data'    => array(
                'firstname' => $customer->firstname,
                'lastname'  => $customer->lastname,
                'order'     => $order->toArray(),
                'comments'  => $comments,
                'status'    => $status
            ),
            'to' =>  $to
        ));
        return $mail->send();
    }

    /**
     * @return array
     */
    public function getStatusHistory()
    {
        return Axis::single('sales/order_status_history')
            ->select('*')
            ->joinLeft('sales_order_status_text',
                'sost.status_id = sosh.order_status_id AND sost.language_id = :languageId',
                'status_name'
            )
            ->bind(array('languageId' => Axis_Locale::getLanguageId()))
            ->where('sosh.order_id = ?', $this->id)
            ->fetchAll()
        ;
    }

    public function getProducts()
    {
        return $this->getTable()->getProducts($this->id);
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        return Axis::single("sales/order_total")
            ->select(array('code', '*'))
            ->where('order_id = ?', $this->id)
            ->fetchAssoc();
    }

    public function getShipping()
    {
        return $this->getTable()->getShipping($this->id);
    }

    public function getTax()
    {
        return $this->getTable()->getTax($this->id);
    }

    public function getShippingTax()
    {
        return $this->getTable()->getShippingTax($this->id);
    }

    public function getTaxAmount()
    {
        return $this->getTax() + $this->getShippingTax();
    }

    public function getSubtotal()
    {
        return $this->getTable()->getSubtotal($this->id);
    }

    /**
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->order_status_id;
    }

    /**
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->order_total;
    }

    /**
     *
     * @return Axis_Object
     */
    public function getBilling()
    {
        $billing = new Axis_Address();

        $country = $this->billing_country ? Axis::single('location/country')->fetchRow(
            $this->getAdapter()->quoteInto('name = ?', $this->billing_country)
        )->toArray() : NULL;

        $zone = $this->billing_state ? Axis::single('location/zone')->fetchRow(
            $this->getAdapter()->quoteInto('name = ?', $this->billing_state)
        )->toArray() : NULL;

        $ret = $billing->setFirstname($this->billing_firstname)
            ->setLastname($this->billing_lastname)
            ->setCompany($this->billing_company)
            ->setStreetAddress($this->billing_street_address)
            ->setSuburb($this->billing_suburb)
            ->setCity($this->billing_city)
            ->setZone($zone)
            ->setPostcode($this->billing_postcode)
            ->setCountry($country)

            ->setPhone($this->billing_phone)
            ->setFax($this->billing_fax)
            ->setAddressFormat($this->billing_address_format_id)
            ->setCustomerId($this->customer_id)
            ->setTaxId(/*@todo*/)
            ;

        return $billing;
    }

    /**
     *
     * @return Axis_Object
     */
    public function getDelivery()
    {
        $delivery = new Axis_Address();

        $country = Axis::single('location/country')->fetchRow(
            $this->getAdapter()->quoteInto('name = ?', $this->delivery_country)
        )->toArray();

        $zone = $this->delivery_state ? Axis::single('location/zone')->fetchRow(
            $this->getAdapter()->quoteInto('name = ?', $this->delivery_state)
        )->toArray() : NULL;

        $delivery->setFirstname($this->delivery_firstname)
            ->setLastname($this->delivery_lastname)
            ->setCompany($this->delivery_company)
            ->setStreetAddress($this->delivery_street_address)
            ->setSuburb($this->delivery_suburb)
            ->setCity($this->delivery_city)
            ->setZone($zone)
            ->setPostcode($this->delivery_postcode)
            ->setCountry($country)
            ->setPhone($this->delivery_phone)
            ->setFax($this->delivery_fax)
            ->setAddressFormat($this->delivery_address_format_id)
            ->setCustomerId($this->customer_id)
            ->setTaxId(/*@todo*/);

        return $delivery;
    }

    /**
     *
     * @return int
     */
    public function getIp()
    {
        return $this->ip_address;
    }

    /**
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->customer_email;
    }

    /**
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function save()
    {
        //auto generate order number
        if (null === $this->number) {
            $this->number = md5(time());
            $id = parent::save();
            $prefix = Axis::config('sales/order/order_number_pattern_prefix');
            $numberPattern = Axis::config('sales/order/order_number_pattern');

            $this->number = $prefix . (strlen($numberPattern) > strlen($id) ?
                substr($numberPattern, 0, strlen($numberPattern) - strlen($id)) . $id : $id);
        }
        return parent::save();

    }

}