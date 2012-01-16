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
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Discount_Model_Discount_Collection // @todo implemets
{
    
    protected $_select;
    
    protected $_filters;
    
    protected $_price = null;


    public function __construct() 
    {
        $select = Axis::model('discount/discount')->select('*');
        
        $select->joinLeft(
                'discount_eav',
                'de.discount_id = d.id',
                '*'
            )
            ->order('d.priority DESC')
            ;
        
        $this->_select = $select;
        
        $this->_filters = new Axis_Object();
    }
    
    public function getSelect() 
    {
        return $this->_select;
    }

    /**
     *
     * @param bool $status
     * @return Axis_Discount_Model_Discount_Select 
     */
    public function addIsActiveFilter($status = true) 
    {
        $this->_select->addIsActiveFilter($status);
        return $this;
    }

    /**
     *
     * @param Axis_Date $date
     * @return Axis_Discount_Model_Discount_Select 
     */
    public function addDateFilter($date = null) 
    {
        $this->_select->addDateFilter($date);
        return $this;
    }
    
   /**
    *
    * @param bool $status
    * @return Axis_Discount_Model_Discount_Select 
    */
    public function addSpecialFilter($status = true)
    {
        $this->_select->addSpecialFilter($status);
        return $this;
    }
    
    public function addSiteFilter($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        $this->_filters->setSite($value);
        return $this;
    }
    
    public function addManufactureFilter($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        $this->_filters->setManufacture($value);
        return $this;
    }
    
    public function addProductIdFilter($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        $this->_filters->setData('productId', $value);
        return $this;
    }
    
    public function addGroupFilter($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        $this->_filters->setGroup($value);
        return $this;
    }
    
    public function addCategoryFilter($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        $this->_filters->setCategory($value);
        return $this;
    }
    
    public function addPriceFilter($value)
    {
        $this->_price = $value;
        return $this;
    }
    
    public function addAtributesFilter($option,  $value)
    {
        return $this;
    }
    
    protected function _load() 
    {
        $select = $this->getSelect();
        $cacheKey = md5($select);
        
        if (!isset($this->_cache[$cacheKey])) {
            $dataset = array();
            $columns = array(
                'id',       'name',         'type',         'amount',
                'priority', 'is_combined',  'from_date',    'to_date'
            );
            
            foreach ($select->fetchAll() as $data) {
                
                if (!isset($dataset[$data['id']])) {
                    foreach ($columns as $column) {
                        $dataset[$data['id']][$column] = $data[$column];
                    }
                }
                
                if (!empty($data['entity'])) {
                    $dataset[$data['id']][$data['entity']][] = $data['value'];
                }
            }
            
//            foreach ($dataset as &$data) {
//                $attributes = array();
//                if (!isset($data['optionId'])) {
//                    continue;
//                }
//                foreach ($data['optionId'] as $optionId) {
//                    if (!isset($data['option[' . $optionId . ']'])) {
//                        continue;
//                    }
//                
//                    foreach ($data['option[' . $optionId . ']'] as $optionValueId) {
//                        $attributes[] = array(
//                            'optionId'      => $optionId,
//                            'optionValueId' => $optionValueId
//                        );
//                    }
////                    unset($data['option[' . $optionId . ']']);
//                }
//                $data['attribute'] = $attributes;
////                unset($data['optionId']);
//            }
            
            $this->_cache[$cacheKey] = $dataset;
        }
        
        return $this->_cache[$cacheKey];
    }

    public function load() 
    {
        $dataset = $this->_load();
        $dataset = $this->_afterLoad($dataset);
        
        return $dataset;
    }
    
    protected function _afterLoad($dataset) 
    {
        $filters = $this->_filters->toArray();
        
        foreach ($dataset as $id => $data) {
            foreach ($filters as $filterName => $filterValue) {

               if (empty($data[$filterName])) {
                   continue;
               }
               
               if (count(array_intersect($data[$filterName], $filterValue))) {
                   continue;
               }
                
                unset($dataset[$id]);
            }
            
            if (null !== $this->_price) {
                
                if (isset($data['price_greate']) 
                    && min($data['price_greate']) > $this->_price) {
                    
                    unset($dataset[$id]);
                }
                
                if (isset($data['price_less']) 
                    && max($data['price_less']) < $this->_price) {
                    
                    unset($dataset[$id]);
                }
            }
            
        }
        
        uasort($dataset, array($this, '_sort'));
        
        return $dataset;
    }
    
    protected function _sort($a, $b) 
    {
        if ($a['priority'] == $b['priority']) {
            return 0;
        }
        return $a['priority'] > $b['priority'] ? -1 : 1;
    }
}