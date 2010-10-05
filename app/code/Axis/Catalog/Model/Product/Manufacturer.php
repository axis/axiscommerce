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
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Manufacturer extends Axis_Db_Table
{
    protected $_name = 'catalog_product_manufacturer';
    
    protected $_dependentTables = array(
        'Axis_Catalog_Model_Product_Manufacturer_Title'
    );
    
    /**
     * Retrieve list of manufacturers
     * @return array
     */
    public function getList()
    {
        return $this->select('*')
            ->joinInner('catalog_product_manufacturer_title',
                'cpm.id = cpmt.manufacturer_id AND language_id = 1',
                '*'
            )
            ->joinLeft('catalog_hurl',
                "ch.key_type = 'm' AND ch.key_id = cpm.id",
                array('url' => 'key_word')
            )
            ->order('cpmt.title ASC')
            ->fetchAll();
    }
    
    /**
     * Get list of manufacturers on all available languages
     * @return array
     */
    public function getListBackend()
    {
        return $this->select('*')
            ->joinLeft(
                'catalog_product_manufacturer_title',
                'cpm.id = cpmt.manufacturer_id',
                '*'
            )
            ->joinLeft(
                'catalog_hurl',
                "ch.key_type = 'm' AND ch.key_id = cpm.id",
                array('url' => 'key_word')
            )
            ->order('cpm.id DESC')
            ->fetchAll()
            ;
    }
    
    /**
     * @param array $data (0 => array(row_data), 1 => )
     * @return boolean
     */
    public function save($data)
    {
        if (!is_array(current($data))) {
            $success = $this->saveRow($data);
        } else {
            $success = true;
            foreach($data as $row) {
                $success = $success ? $this->saveRow($row) : false;
            }
        }
        if ($success) {
            Axis::message()->addSuccess(
                Axis::translate('catalog')->__(
                    'Data was successfully saved'
                )
            );
        }
        return $success;
    }
    
    /**
     * Update or delete manufacturer row
     * Checks is recieved url has duplicate before save. 
     * If it has - return false
     * 
     * @param array $row
     * @return bool
     */
    public function saveRow($row)
    {
        $manufacturer = false;
        if (isset($row['id']) && !empty($row['id'])) {
            $manufacturer = $this->find($row['id'])->current();
        }
        
        $url = trim($row['url']);
        if (empty($url)) {
            $url = $row['name'];
        }
        $url = preg_replace('/[^a-zA-Z0-9]/', '-', $url);
        if (Axis::single('catalog/hurl')->hasDuplicate(
                $url, 
                array_keys(Axis_Collect_Site::collect()), 
                $manufacturer ? $manufacturer->id : null
            )) {
            
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Duplicate entry (url)'
                )
            );
            return false;
        }
        
        if (!$manufacturer) {
            unset($row['id']);
            $manufacturer = $this->createRow();
        }
        $row['image'] = empty($row['image']) ? '' : '/' . trim($row['image'], '/');
        $manufacturer->setFromArray($row);
        if (false === $manufacturer->save()) {
            return false;
        }
        
        $success = true;
        
        // title
        Axis::single('catalog/product_manufacturer_title')->delete(
            'manufacturer_id = ' . $manufacturer->id
        );
        $modelManufactureTitle =  Axis::single('catalog/product_manufacturer_title');
        foreach (Axis_Collect_Language::collect() as $id => $lang) {
            $success = $success ? (bool) $modelManufactureTitle->insert(array(
                'manufacturer_id' => $manufacturer->id,
                'language_id' => $id,
                'title' => isset($row['title_' . $id]) ? 
                    $row['title_' . $id] : ''
            )) : false;
        }
        
        // url
        foreach (Axis_Collect_Site::collect() as $id => $name) {
            $success = $success ? (bool) Axis::single('catalog/hurl')->save(
                array(
                    'site_id'  => $id,
                    'key_id'   => $manufacturer->id,
                    'key_type' => 'm',
                    'key_word' => $url
            )) : false;
        }
        
        return $success;
    }
    
    public function deleteByIds($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $success = (bool) $this->delete(
            Axis::db()->quoteInto('id IN(?)', $ids)
        );
        if (!$success) {
            return false;
        }
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Manufacturer was deleted successfully'
            )
        );
        Axis::single('catalog/hurl')->delete(
            Axis::db()->quoteInto("key_type = 'm' AND key_id IN (?)", $ids)
        );
        return $success;
    }
}