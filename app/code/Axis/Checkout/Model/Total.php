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
 * @copyright   Copyright 2008-2012 Axis
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
     * Recollefct totals flag
     *
     * @var boolean
     */
    private $_recollect = false;

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
            try {
                $method->collect($this);
            } catch (Exception $e) {
                continue;
            }
        }
        $this->_recollect = false;
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
        if ($this->_recollect || null === $this->_collects) {
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
            if (!$this->_recollect
                && null !== $this->_collects
                && isset($this->_collects[$code])) {

                $total = $this->_collects[$code]['total'];
            } else {
                $methodName = str_replace('Checkout_', '', $code);
                $method = $this->getMethod($methodName);
                if ($method->isEnabled()) {
                    $resetCollects = (null === $this->_collects);
                    $method->collect($this);
                    $total = $this->_collects[$code]['total'];
                    if ($resetCollects) {
                        $this->_collects = null;
                    }
                } else {
                    $total = 0;
                }
            }
        } else {
            if ($this->_recollect || null === $this->_collects) {
                $this->_runCollects();
            }
            $total = 0;
            foreach ($this->_collects as $collect) {
                $total += $collect['total'];
            }
            if ($total < 0) {
                $total = 0;
            }
        }
        return $total;
    }

    /**
     * Retrieve enabled total models
     *
     * @return array
     */
    protected function _getMethods()
    {
        foreach (Axis::config('orderTotal') as $code => $config) {
            if (!$config->enabled) {
                continue;
            }
            if (!isset($this->_methods[$config->model])) {
                $this->getMethod($config->model);
            }
        }
        return $this->_methods;
    }

    /**
     * Retrieve model by code or model alias. Model is saved to $_methods
     *
     * @param string $model Total code or alias to the model
     * @return Axis_Checkout_Model_Total_Abstract
     */
    public function getMethod($model)
    {
        if (!strstr($model, '/')) { // code is received
            $model = Axis::config("orderTotal/{$model}/model");
        }
        if (!isset($this->_methods[$model])) {
            $this->_methods[$model] = Axis::model($model);
        }
        return $this->_methods[$model];
    }

    public function setRecollect($flag)
    {
        $this->_recollect = $flag;
    }
}
