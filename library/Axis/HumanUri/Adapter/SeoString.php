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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Catalog
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_HumanUri_Adapter_SeoString extends Axis_HumanUri_Adapter_Abstract
{
    /**
     * Initialize params
     */
    protected function _init()
    {

        $params = array();
        $expectedKeys = $this->getExpectedKeys();
        $attributes = array();
        foreach ($this->_request->getParams() as $name => $value) {
            if (in_array($name, $expectedKeys)) {
                $params[$name]['value'] = $value;
            } elseif ($this->_isAttribute($name)) {
                $params[$name]['value'] = $value;
                list(,$optionId) = explode('_', $name);
                $attributes[$optionId] = $value;
            }
        }
        if (sizeof($attributes))
            $params['attributes'] = $attributes;

        /*
         * Convert price from string('0,100') to array('from' => 0, 'to' => 100)
         */
        if (!empty($params['price'])) {
            list($from, $to) = explode(',', $params['price']['value']);
            $params['price'] = array(
                'value' => array('from' => $from, 'to' => $to)
            );
        }

        $this->_params = $params;
        $this->_loadSeoData();
    }

    private function _loadSeoData()
    {
        if (isset($this->_params['cat'])) {
            $this->_params['cat']['seo'] = Axis::single('catalog/hurl')
                ->select('key_word')
                ->where('key_id = ?', $this->_params['cat']['value'])
                ->where("key_type = 'c'")
                ->where('site_id = ?', Axis::getSiteId())
                ->fetchOne();
        }

        if (isset($this->_params['manufacturer'])) {
            $this->_params['manufacturer']['seo'] = strtolower(
                Axis::single('catalog/product_manufacturer')->getNameById(
                    $this->_params['manufacturer']['value']
                )
            );
        }


        if (isset($this->_params['attributes'])) {
/*//
            //exit();
            $rows = Axis::single('catalog/product_option_text')
                ->getOptionsByAttributtes(
                    $this->_params['attributes']
                );

            foreach ($rows as $row) {
                $this->_params['at_' . $row['option_id']] = array(
                    'value' => $row['id'],
                    'seo' => strtolower($row['value_name']),
                    'option_name' => $row['option_name'],
                    'value_name' => $row['value_name']
                );
            }*/
            $this->_params['attributes'] = Axis::single('catalog/product_option')
                ->getAttributesByKeyword($this->_params['attributes']);
        }
    }

    /**
     * Build new SEO string
     *
     * @param $options url options
     * @return string
     */
    private function _getSeoString($options, $reset = false)
    {
        $seo = array();
        foreach ($this->getSeoKeys() as $key) {
            if (!$reset && $key == 'product')
                continue;
            if (!empty($options[$key]['seo'])) {
                $seo[] = $options[$key]['seo'];
            } elseif (!$reset && !empty($this->_params[$key]['seo']) &&
                      !array_key_exists($key, $options)) {
                $seo[] = $this->_params[$key]['seo'];
            }
        }

        if (isset($this->_params['attributes']) && !$reset) {
            foreach ($this->_params['attributes'] as $optionId => $optionValueId) {
                if (!array_key_exists('at_' . $optionId, $options))
                    $seo[] = $this->_params['at_' . $optionId]['seo'];
            }
        }

        foreach ($options as $key => $value) {
            if ($this->_isAttribute($key) && isset($value['seo']))
                $seo[] = $value['seo'];
        }

        return str_replace('/', '-', implode('-', $seo));
    }

    /**
     * Convert humanUrl-options to ZF url options
     *
     * @param array options
     * @return array
     */
    private function _getUrlOptions(array $options, $reset = false)
    {
        $urlOptions = array();
        $urlOptions['s'] = $this->_getSeoString($options, $reset);
        foreach ($options as $key => $value) {
            if (is_array($value) && !empty($value['value'])){
                $urlOptions[$key] = $value['value'];
            } else {
                $urlOptions[$key] = $value;
            }
        }

        return $urlOptions;
    }

    /**
     * Build humanUrl
     *
     * @return string
     */
    public function url($options = array(), $reset = false)
    {

        $route = Zend_Controller_Front::getInstance()->getRouter()->getRoute('default');
        
        $urlOptions = $this->_getUrlOptions($options, $reset);
        if (empty($options['product'])) {
            $urlOptions['product'] = null;
        }

        return '/' . $route->assemble($urlOptions, $reset);
    }
}