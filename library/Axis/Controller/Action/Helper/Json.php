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
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Action_Helper
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Action_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Action_Helper_Json extends Zend_Controller_Action_Helper_Json
{
    /**
     *
     * @var array
     */
    protected $_data;

    /**
     * Encode JSON response and immediately send
     *
     * @param  mixed   $data
     * @param  boolean|array $keepLayouts
     * NOTE:   if boolean, establish $keepLayouts to true|false
     *         if array, admit params for Zend_Json::encode as enableJsonExprFinder=>true|false
     *         if $keepLayouts and parmas for Zend_Json::encode are required
     *         then, the array can contains a 'keepLayout'=>true|false
     *         that will not be passed to Zend_Json::encode method but will be passed
     *         to Zend_View_Helper_Json
     * @param  bool $wrap
     * @return string|void
     */
    public function sendJson($data, $keepLayouts = false, $wrap = true)
    {
        if (!is_array($data)) {
            return parent::sendJson($data, $keepLayouts);
        }
        if (isset($data['success']) && !is_bool($data['success'])) {
            $data['success'] = (bool) $data['success'];
        }

        if ($wrap) {

            $messages = Axis::message()->getAll();
//            if (isset(false === $data['success']) && $data['success']) {
//                unset($messages['success']);
//            }

            $data = array_merge(array('messages' => $messages), $data);
        }

        if ($this->_data) {
            $data = array_merge($this->_data, $data);
        }

        $data = $this->encodeJson($data, $keepLayouts);
        $response = $this->getResponse();
        $response->setBody($data);

        if (!$this->suppressExit) {
            Zend_Wildfire_Channel_HttpHeaders::getInstance()->flush(); //Axis_FirePhp
            $response->sendResponse();
            exit;
        }

        return $data;
    }

    /**
     * Encode JSON response and immediately send
     *
     * @param mixed $data
     * @return string|void
     */
    public function sendRaw($data)
    {
        return $this->sendJson($data, false, false);
    }

    /**
     * Encode JSON response and immediately send
     * with success => true.
     * @param  mixed   $data
     * @param  boolean|array $keepLayouts
     * @return string|void
     */
    public function sendSuccess($data = array(), $keepLayouts = false)
    {
        if (is_bool($data)) {
            return $this->sendJson(
                array('success' => $data), $keepLayouts, true
            );
        }
        $data = array_merge($data, array('success' => true));
        return $this->sendJson($data, $keepLayouts, true);
    }

    /**
     * Encode JSON response and immediately send
     * success => false
     * @param  mixed   $data
     * @param  boolean|array $keepLayouts
     * @return string|void
     */
    public function sendFailure($data = array(), $keepLayouts = false)
    {
        $data = array_merge($data, array('success' => false));
        return $this->sendJson($data, $keepLayouts, true);
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return Axis_Controller_Action_Helper_Json Fluent interface
     */
    protected  function _setData($name, $value)
    {
        $name = strtolower(preg_replace(
            array('/(.)([A-Z])/', '/(.)(\d+)/'), "$1_$2", $name
        ));
        $this->_data[$name] = $value;
        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return Axis_Controller_Action_Helper_Json Fluent interface
     */
    public function __set($name, $value)
    {
        return $this->_setData($name, $value);
    }

    /**
     *
     * @param string $name
     * @param mixed $argunents
     * @return mixed
     * @throws Axis_Exception
     */
    public function __call($name, $argunents)
    {
        if (!count($argunents)) {
            $argunents[] = null;
        }
        if ('set' === substr($name, 0, 3)) {
            return $this->_setData(substr($name, 3), $argunents[0]);
        }
        throw new Axis_Exception(Axis::translate('core')->__(
            "Call to undefined method '%s'", get_class($this) . '::' . $name
        ));
    }
}
