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
abstract class Axis_Method_Shipping_Model_Abstract extends Axis_Method_Abstract
{
    protected $_section = 'shipping';
    protected $_file = __FILE__;

    /**
     * @var string
     */
    protected $_type = null;

    /**
     * @var array
     */
    protected $_types = array();

    /**
     * Construct shipping class method
     *
     * @param string $type
     */
    public function __construct($type = null)
    {
        parent::__construct();

        //fix  single call
        if (is_array($type) && !count($type)) {
            $type = null;
        }

        $this->_type = $type;
        try {
            $writer = new Zend_Log_Writer_Stream(
                Axis::config('system/path') .
                Axis::config('log/main/shipping')
            );
            $this->_logger = new Zend_Log($writer);
        } catch (Exception $e) {}
    }

    /**
     * Loging
     *
     * @param mixed $message
     */
    public function log($message)
    {
        if ($this->_config->showErrors) {
            Axis::message()->addError($this->_code . ': ' . $message);
        }
        $this->_logger->info($this->_code . ' ' . $message);
    }

    /**
     * Сan not used other method this class on this method
     *
     * @param mixed array $request shipping request
     * @return mixed array
     */
    abstract function getAllowedTypes($request);

    /**
     * Return Shipping Method
     *
     * @param $request Shipping request
     * @param $code shipping code
     * @return Axis_Method_Shipping_Model_Abstract
     * @throws Axis_Exception if none found
     */
    public function getType($request, $code)
    {
        if (!Zend_Registry::isRegistered('shippingType')) {
            $types = $this->getAllowedTypes($request);
            if (!count($types)) {
                throw new Axis_Exception(
                    Axis::translate('checkout')->__(
                        'Shipping Method does not exists for this shipping request'
                    )
                );
            }
            foreach ($types as $type) {
                if ($type['id'] == $code) {
                    Zend_Registry::set('shippingType', $type);
                    return $type;
                }
            }
            throw new Axis_Exception(
                Axis::translate('checkout')->__(
                    'Shipping Method not found'
                )
            );
        }
        return Zend_Registry::get('shippingType');
    }

    /**
     *
     * @param $request shipping request
     * @return bool
     */
    public function isAllowed($request)
    {
        if (!empty($this->_config->minOrderTotal) &&
            $request['price'] < $this->_config->minOrderTotal) {

            return false;
        }

        if (!empty($this->_config->maxOrderTotal) &&
            $request['price'] > $this->_config->maxOrderTotal) {

            return false;
        }

        if (empty($this->_config->geozone) || !intval($this->_config->geozone)) {
            return true;
        }

        if (null !== $request['payment_method_code']) {
            $payment = Axis_Payment::getMethod($request['payment_method_code']);
            $disallowedShippingMethods = $payment->config()->shippings->toArray();
            if (in_array($this->getCode(), $disallowedShippingMethods)) {
                return false;
            }
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
     * Return translated shipping method title
     * (Dont use on getTypes function current method)
     *
     * @see library/Axis/Method/Axis_Method_Abstract#getTitle()
     * @return string
     */
    public function getTitle()
    {
        $title = isset($this->_config->title) ?
            $this->_config->title : $this->_title;

        if (null !== $this->_type &&
            $request = Axis::single('checkout/checkout')->getShippingRequest()) {

            $type = $this->getType($request, $this->getCode());
            $title = $type['title'];
        }
        return $this->getTranslator()->__($title);
    }

    /**
     * Return shipping code
     *
     * @see library/Axis/Method/Axis_Method_Abstract#getCode()
     * @param boolean $includeType
     * @return string
     */
    public function getCode($includeType = true)
    {
//        if ($type = current(func_get_args())) {
//            $this->_type = $type;
//        }
        if ($includeType && null !== $this->_type) {
            return $this->_code . '_' . $this->_type;
        }
        return $this->_code;
    }

    /**
     * Only Axis shippings are supported
     * @todo return module with category name
     *  example: Axis_ShippingFlat
     *
     * @return Axis_Translator
     */
    public function getTranslator()
    {
        $codeArray = explode('_', $this->_code);
        return Axis::translate('Shipping' . current($codeArray));
    }
}