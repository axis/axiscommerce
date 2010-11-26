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
 * @subpackage  Axis_Checkout_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Model_Total
{
    /**
     * Array of Axis_Checkout_Model_Total_ methods
     *
     * @var array
     */
    private $_methods = array();
    
    /**
     * Collects array, content order_total information
     *
     * @var array
     */
    private $_collects = null;
    
    /**
     * Apply all total methods for current total
     * @return void
     */
    protected function _runCollects()
    {
        $this->_collects = array();
        foreach ($this->_getMethods() as $method) {
            if (!$method->isEnabled()) {
                continue;
            }
            $method->collect($this);
        }
        uasort($this->_collects, array($this, '_sortCollects'));
    }

    /**
     * Functions for sorting collects
     * @param $a
     * @param $b
     * @return int
     */
    private function _sortCollects($a, $b)
    {
        if ($a['sortOrder'] == $b['sortOrder']) {
            return 0;
        }
        return ($a['sortOrder'] > $b['sortOrder']) ? 1 : -1;
    }
    
    public function getCollects()
    {
        if (null === $this->_collects) {
            $this->_runCollects();
        }
        
        return $this->_collects;
    }

    /**
     * Add new total collect
     *
     * @param array $collect
     */
    public function addCollect($collect)
    {
        $this->_collects[$collect['code']] = $collect;
    }
    
    /**
     * Retrieve final total sum for payment, including shipping price and taxes
     * 
     * @param string $code module code
     * @return float
     */
    public function getTotal($code = null)
    {
        if (!empty($code)) {
            if (null !== $this->_collects && isset($this->_collects[$code])) {
                $total = $this->_collects[$code]['total'];
            } else {
                $methodName = str_replace('Checkout_', '', $code);
                $method = $this->getMethod($methodName);
                if ($method->isEnabled()) {
                    $method->collect($this);
                    $total = $this->_collects[$code]['total'];
                    $this->_collects = null;
                } else {
                    $total = 0;
                }
            }
        } else {
            if (null === $this->_collects) {
                $this->_runCollects();
            }
            $total = 0;
            foreach ($this->_collects as $collect) {
                $total += $collect['total'];
            }
        }
        return $total;
    }
    
    /**
     * Return list of order totals
     * 
     * @return array
     */
    protected function _getMethodNames()
    {
        if ($methods = Axis::cache()->load('order_total_methods')) {
            return $methods;
        }
        $dirPath = realpath(
            Axis::config()->system->path . '/app/code/Axis/Checkout/Model/Total'
        );
        $methods = array();
        $skip = array('Abstract');
        $dp = opendir($dirPath);
        while ($fname = readdir($dp)) {
            if (!is_file("{$dirPath}/{$fname}")) {
                continue;
            }
            list($name, $ext) = explode('.', $fname, 2);
            if (in_array($name, $skip) || $ext != 'php') {
                continue;
            }
            $methods[] = $name;
        }
        closedir($dp);
        Axis::cache()->save($methods, 'order_total_methods', array('modules'));
        return $methods;
    }
    
    /**
     * Retrieve Axis_Checkout_Model_Total_ methods list
     * 
     * @return array
     */
    protected function _getMethods()
    {
        foreach ($this->_getMethodNames() as $name) {
            if (!isset($this->_methods[$name])) {
                $this->getMethod($name);
            }
        }
        return $this->_methods;
    }
    
    /**
     * Retrieve method by name. Method is saved to $_methods
     * 
     * @param string $code
     * @return Axis_Checkout_Model_Total_Abstract
     */
    public function getMethod($code)
    {
        if(false === function_exists('camelize')) {
            function camelize($str) {
                $str = ltrim(str_replace(" ", "", ucwords(str_replace("_", " ", $str))));
                return (string)(strtolower(substr($str, 0, 1)) . substr($str, 1));
            }
        }
        if (!isset($this->_methods[$code])) {
            $className = 'Axis_Checkout_Model_Total_' . ucfirst(camelize($code));
            $this->_methods[$code] = new $className();
        }
        return $this->_methods[$code];
    }
}