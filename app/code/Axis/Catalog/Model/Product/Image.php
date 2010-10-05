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
class Axis_Catalog_Model_Product_Image extends Axis_Db_Table
{
    protected $_name = 'catalog_product_image';
    protected $_rowClass = 'Axis_Catalog_Model_Product_Image_Row';
    protected $_referenceMap = array(
        'Product' => array(
            'columns'           => 'product_id',
            'refTableClass'     => 'Axis_Catalog_Model_Product',
            'refColumns'        => 'id'
        )
    );
    
    /**
     * @param mixed $productIds
     * @return array [product_id => images_array, ...]
     */
    public function getList($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        
        $select = $this->getAdapter()->select()
            ->from(array('cpi' => $this->_prefix . 'catalog_product_image'))
            ->joinLeft(array('cpit' => $this->_prefix . 'catalog_product_image_title'),
                'cpi.id = cpit.image_id AND cpit.language_id = ' . Axis_Locale::getLanguageId(),
                'title'
            )
            ->where('cpi.product_id IN (?)', $productIds)
            ->order('cpi.sort_order')
            ->order('cpi.id DESC');
        
        $result = array_fill_keys($productIds, array());
        foreach ($this->getAdapter()->fetchAssoc($select) as $id => $image) {
            $result[$image['product_id']][$id] = $image;
        }
        
    	return $result;
    }
    
    /**
     * Same as the getList, but returns all languages
     * 
     * @param int $productId
     * @return array
     */
    public function getListBackend($productId)
    {
        $select = $this->getAdapter()->select()
            ->from(array('cpi' => $this->_prefix . 'catalog_product_image'))
            ->joinLeft(array('cpit' => $this->_prefix . 'catalog_product_image_title'),
                'cpi.id = cpit.image_id',
                array('title', 'language_id')
            )
            ->where('cpi.product_id = ?', $productId)
            ->order('cpi.sort_order')
            ->order('cpi.id DESC');
        
        return $this->getAdapter()->fetchAll($select);
    }
}