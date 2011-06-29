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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_HumanUri
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_HumanUri
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_HumanUri_Adapter_Abstract
{
    /**
     * Instance of Zend_Controller_Request_Abstract
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_params = array();

    public function getExpectedKeys()
    {
        return array_merge($this->getSeoKeys(), $this->getSimpleKeys());
    }

    public function getSeoKeys()
    {
        return array('manufacturer', 'cat', 'product', 'attribute');
    }

    public function getSimpleKeys()
    {
        return array('price', 'page', 'order', 'limit', 'dir', 'mode');
    }

    public function __construct()
    {
         $this->_request = Zend_Controller_Front::getInstance()->getRequest();
         $this->_init();
    }

    /**
     * Return the request object.
     *
     * @return Zend_Controller_Request_Abstract
     */
    final public function getRequest()
    {
        return $this->_request;
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    protected function _isAttribute($key)
    {
        if (false === strpos($key, '_')) {
            return false;
        }
        if ('at' == current(explode('_', $key))) {
            return true;
        }
        return false;
    }

    public function hasParam($key)
    {
        return isset($this->_params[$key]);
    }

    public function getParamValue($key)
    {
        if (isset($this->_params[$key]['value'])) {
            return $this->_params[$key]['value'];
        }
        return null;
    }

    public function getAttributeIds()
    {
        $ids = array();
        if (isset($this->_params['attributes'])) {
            foreach ($this->_params['attributes'] as $oId => $item) {
                $ids[$oId] = $item['value'];
            }
        }
        return $ids;
    }

    public function getParamSeo($key)
    {
        if (isset($this->_params[$key]['seo'])) {
            return $this->_params[$key]['seo'];
        }
        return null;
    }

    public function getParam($key, $default = null)
    {
        if (null === $key) {
            return $this->_params;
        }
        if (strstr($key, '/')) {
            $result = $this->_params;
            foreach (explode('/', $key) as $key) {
                if (!is_array($result) || !isset($result[$key])) {
                    return $default;
                }
                $result = $result[$key];
            }
            return $result;
        }
        return $this->hasParam($key) ? $this->_params[$key] : $default;
    }

    public function setParam($key, $value)
    {
        $filter = new Zend_Filter_HtmlEntities();
        $this->_params[$key] = $filter->filter($value);
    }

    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Put uri params to _params array
     * _params example
     * array(
     *     's' => 'seo string',
     *     'cat' => array('value' => '18', 'seo' => 'blabla'),
     *     'product' => array('value' => '25', 'seo' => 'trulala pro edition'),
     *     'manufacturer' => array('value' => '8', 'seo' => 'trulala pro edition'),
     *     'at_10' => array('value' => 13, 'seo' => 'color_red');
     *     'price' => array('from' => '100', 'to' => '200'),
     *     'sort' => 'name',
     *     'limit' => '10',
     *     'dir' => 'asc',
     *     'page' => '1'
     * )
     */
    abstract protected function _init();

    abstract function url($options = array(), $reset = false);
}