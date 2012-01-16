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
 * @subpackage  Axis_Catalog_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Hurl extends Axis_Db_Table
{
    protected $_name = 'catalog_hurl';
    protected $_primary = array('key_word', 'site_id');
    
    /**
     *
     * @param int $productId
     * @return string 
     */
    public function getProductUrl($productId)
    {
        $keyWord = $this->select('key_word')
            ->where("key_type = 'p'")
            ->where('key_id = ?', $productId)
            ->fetchOne();
        return $keyWord ? $keyWord : '';
    }
    
    /**
     *
     * @param array $data
     * @return Axis_Db_Table_Row 
     */
    public function save(array $data)
    {
        $row = $this->select()
            ->where('site_id = ?', $data['site_id'])
            ->where('key_id = ?', $data['key_id'])
            ->where('key_type = ?', $data['key_type'])
            ->fetchRow()
            ;
        if (!$row) {
            $row = $this->createRow();
        }
        $row->setFromArray($data);
        $row->save();
        return $row;
    }
    
    /**
     * Detects is url is already in table
     * 
     * @param string $keyWord
     * @param int|array $siteId
     * @param int $keyId - id to skip
     * @return bool
     */
    public function hasDuplicate($keyWord, $site = null, $keyId = null)
    {
        if (null === $site) {
            $site = Axis::getSiteId();
        }
        if (!is_array($site)) {
            $site = array($site);
        }
        $select = $this->select()
            ->where('key_word = ?', $keyWord)
            ->where('site_id IN (?)', $site);
            
        if ($keyId) {
            $select->where('key_id <> ?', $keyId);
        }
        
        return !is_null($select->fetchRow());
    } 
}