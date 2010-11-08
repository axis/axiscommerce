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
class Axis_HumanUri_Adapter_Readable extends Axis_HumanUri_Adapter_Abstract
{
    protected function _init()
    {
        $simpleKeys = $this->getSimpleKeys();
        
        $seoParams = array();
        $attributeParams = array();
        foreach ($this->getKeywords() as $keyword) {

            if (false === strpos($keyword, '=')) {
                $seoParams[] = $keyword;
                continue;
            } 
            list($key, $value) = explode('=', $keyword, 2);
            if (in_array($key, $simpleKeys)) {
                $this->setParam($key, $value);
            } else {
                $attributeParams[$key] = $value;
            }
        }
        
        if (sizeof($seoParams)) {
            $this->_initSeoParams($seoParams);
        }
        
        if (sizeof($attributeParams)) {
            $this->_initAttributeParams($attributeParams);
        }
    }
    
    private function _initAttributeParams($keywords)
    {
        $this->_params['attributes'] = Axis::single('catalog/product_option')
            ->getAttributesByKeyword($keywords);
    }

    private function _initSeoParams($keywords)
    {
        if (!sizeof($keywords)) {
            return array();
        }

        $rowset = Axis::single('catalog/hurl')
            ->select()
            ->where('key_word IN (?)', $keywords)
            ->where('site_id = ?', Axis::getSiteId())
            ->fetchAssoc()
            ;

        foreach ($rowset as $row) {

            $categoryId = $productId = $manufacturerId = $row['key_id'];
            switch ($row['key_type']) {
                case 'c':
                    $this->_params['cat']['value'] = $categoryId;
                    if (empty($this->_params['cat']['seo'])) {
                        $this->_params['cat']['seo'] = $row['key_word'];
                    } else {
                        $this->_params['cat']['seo'] .= '/' . $row['key_word'];
                    }
                    break;
                case 'p':
                    $this->_params['product']['value'] = $productId;
                    $this->_params['product']['seo'] = $row['key_word'];
                    $this->getRequest()->setParam('product', $productId);
                    break;
                case 'm':
                    $this->_params['manufacturer']['value'] = $manufacturerId;
                    $this->_params['manufacturer']['seo'] = $row['key_word'];
                    $this->_params['manufacturer']['title'] =
                        Axis::single('catalog/product_manufacturer_title')
                            ->select('title')
                            ->where('manufacturer_id = ?', $manufacturerId)
                            ->where('language_id = ?', Axis_Locale::getLanguageId())
                            ->fetchOne();
                    break;
                default:
                    break;
            }
        }
    }

    public function getKeywords()
    {
        $path = urldecode($this->getRequest()->getPathInfo());
        $keywords = explode('/', trim($path, '/'));
        array_shift($keywords); //remove root catalog from array
        return $keywords;
    }
    
    public function url($options = array(), $reset = false)
    {
        $url = '/';
        foreach ($this->getSeoKeys() as $key) {
            if (!empty($options[$key]['seo'])) {
                $url .= $options[$key]['seo'] . '/';
            } elseif (!$reset && !array_key_exists($key, $options) 
                && !empty($this->_params[$key]['seo'])) {

                $url .= $this->_params[$key]['seo'] . '/';
            }
        }
        
        if ($this->hasParam('attributes')) {
            foreach ($this->_params['attributes'] as $id => $item) {
                if ((!isset($options['attributes']) 
                        || !array_key_exists($id, $options['attributes']))
                    && !$reset) {

                    $url .= $item['seo'] . '/';
                }
            }
        }
        
        if (isset($options['attributes'])) {
            foreach ($options['attributes'] as $item) {
                if (!empty($item['seo'])) {
                    $url .= $item['seo'] . '/';
                }
            }
        }
        
        foreach ($this->getSimpleKeys() as $key) {

            if (!empty($options[$key])) {
                $url .= $key . '=' . $options[$key] . '/';
            } elseif (!$reset
                      && !array_key_exists($key, $options)
                      && !empty($this->_params[$key])) {

                $url .= $key . '=' . $this->_params[$key] . '/';
            }
        }
        
        return str_replace(' ', '+', $url);
    }
}