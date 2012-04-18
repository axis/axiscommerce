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
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Method
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Method
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Method_Payment_Model_Abstract extends Axis_Method_Abstract
{
    /**
     * Payment Method unique code
     */
    protected $_section = 'payment';
    protected $_file = __FILE__;

    /**
     * Checkout data storage
     *
     * @var Zend_Session_Namespace
     */
    private $_storage;

    public function __construct()
    {
        parent::__construct();
        try {
            $writer = new Zend_Log_Writer_Stream(
                Axis::config()->system->path .
                Axis::config()->log->main->payment
            );
            $this->_logger = new Zend_Log($writer);
        } catch (Exception $e) {}
    }

    /**
     * Validation of order data by payment service
     * should be called from this method
     *
     * If invalid data received methods should throw an Exception
     *
     * Method can return a redirect url:
     * array(
     *  'redirect' => payment aggregator url
     * )
     */
    public function preProcess() {}

    /**
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function pending(Axis_Sales_Model_Order_Row $order) { return true;}

    /**
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function processing(Axis_Sales_Model_Order_Row $order) {return true;}

    /**
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function cancel(Axis_Sales_Model_Order_Row $order) {return true;}

    /**
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function refund(Axis_Sales_Model_Order_Row $order) {return true;}

    /**
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function failed(Axis_Sales_Model_Order_Row $order) {return true;}

    /**
     * @param Axis_Sales_Model_Order_Row $order
     *
     * Method can return a redirect url:
     * array(
     *  'redirect' => payment aggregator url
     * )
     */
    public function postProcess(Axis_Sales_Model_Order_Row $order) { }

    /**
     * Return checkout data storage
     *
     * @return Zend_Session_Namespace
     */
    public function getStorage()
    {
        if (null === $this->_storage) {
            $this->_storage = new Zend_Session_Namespace(
                $this->_code . '-Checkout'
            );
            $this->_storage->setExpirationSeconds(600);
            $this->_storage->setExpirationHops(5);
        }
        return $this->_storage;
    }

    /**
     * Saved custom attributes
     * @see (form.phtml, setPaymentAction)
     * @return bool
     * @param array $data
     */
    public function saveData($data)
    {
        return $this->getStorage()->data = $data;
    }

    /**
     * Clear payment storage
     */
    public function clear()
    {
        $this->getStorage()->unsetAll();
    }

    /**
     *
     * @param string $message
     */
    public function log($message)
    {
        //Axis::message()->addError($message);
        $this->_logger->info($message);
    }

    /**
     *
     * @param array $request
     * @return bool
     */
    public function isAllowed($request)
    {
        // Zend_Debug::dump($request);die;
        if (isset($this->_config->minOrderTotal) &&
            !empty($this->_config->minOrderTotal) &&
            $request['price'] < $this->_config->minOrderTotal)
        {
            return false;
        }

        if (isset($this->_config->maxOrderTotal) &&
            !empty($this->_config->maxOrderTotal) &&
            $request['price'] > $this->_config->maxOrderTotal)
        {
            return false;
        }

        if (null !== $request['shipping_method_code']) {
            $shipping = Axis_Shipping::getMethod($request['shipping_method_code']);
            // get list of disallowed payment methods, and compare with requested payment method
            $disallowedPaymentMethods = $shipping->config()->payments->toArray();
            if (in_array($this->getCode(), $disallowedPaymentMethods)) {
                return false;
            }

            // get list of disallowed shippings and compare with selected shipping method
            $disallowedShippingMethods = $this->_config->shippings->toArray();
            if (in_array($shipping->getCode(false), $disallowedShippingMethods)) {
                return false;
            }
        }

        if (!isset($this->_config->geozone)
            || !intval($this->_config->geozone)) {

            return true;
        }

        if (empty($request['country']['id'])) {
            return true;
        }

        $zoneId = null;
        if (isset($request['zone']['id'])) {
            $zoneId = $request['zone']['id'];
        }

        return Axis::single('location/geozone_zone')->inGeozone(
            $this->_config->geozone,
            $request['country']['id'],
            $zoneId
        );

    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $title = isset($this->_config->title) ?
            $this->_config->title : $this->_title;

        return $this->getTranslator()->__($title);
    }

    /**
     * Convert abstract total of order
     * to payment baseCurrency
     *
     * @param float $amount
     * @return float
     */
    public function getAmountInBaseCurrency($amount)
    {
        return Axis::single('locale/currency')
            ->to($amount, $this->getBaseCurrencyCode());
    }

    /**
     * Retrieve baseCurrency code
     * Used for online transactions
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        $baseCurrency = $this->_config->currency;
        if (null === $baseCurrency) {
            $baseCurrency = Axis::config('locale/main/baseCurrency');
        }
        return (null === $baseCurrency || strlen($baseCurrency) != 3) ?
            'USD' : $baseCurrency;
    }

    /**
     * Only Axis payments are supported
     * @todo return module with category name
     *  example: Axis_PaymentPaypal
     *
     * @return Axis_Translator
     */
    public function getTranslator()
    {
        $codeArray = explode('_', $this->_code);
        return Axis::translate('Payment' . current($codeArray));
    }
}