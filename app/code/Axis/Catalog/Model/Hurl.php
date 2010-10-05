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
class Axis_Catalog_Model_Hurl extends Axis_Db_Table
{
    protected $_name = 'catalog_hurl';
    protected $_primary = array('key_word', 'site_id');
    
    public function getProductUrl($productId)
    {
        $where = array(
            "key_type = 'p'",
            'key_id = ' . $productId
        );
        $row = $this->fetchRow($where);
        if ($row) {
            return $row->key_word;
        }
        return '';
    }
    
    public function save($data)
    {
        $where = array(
            'site_id = ' . $data['site_id'],
            'key_id = ' . $data['key_id'],
            "key_type = '{$data['key_type']}'" 
        );
        if (!$row = $this->fetchRow($where)) {
            $row = $this->createRow();
        }
        $row->setFromArray($data);
        return $row->save();
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
        
        return !is_null($this->fetchRow($select));
    } 
}