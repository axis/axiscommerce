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
 * @package     Axis_Core
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Config_Value extends Axis_Db_Table
{
    protected $_name = 'core_config_value';

    /**
     *
     * @param string $path
     * @param int $siteId
     * @return <type>
     */
    public function getValue($path, $siteId)
    {
        $where = array(
            $this->getAdapter()->quoteInto('path = ?', $path),
            $this->getAdapter()->quoteInto(
                'site_id IN(?)', array_unique(array('0', $siteId))
            )
        );
        $row = $this->fetchRow($where, 'site_id desc');
        if ($row) {
            return $row->value;
        }
        return '';
    }

    /**
     *
     * @param string $path
     * @param array $siteIds
     * @return array
     */
    public function getValues($path, $siteIds = null)
    {
        
        if (!$siteIds) {
            $siteIds = array_keys(Axis_Collect_Site::collect());
        }
        $siteIds[count($siteIds) + 1] = 0;
        
        $select = $this->getAdapter()->select();
        $select->from(array('cv' => $this->_prefix . 'core_config_value'),
            array('site_id', 'value')
        );
        $select->where($this->getAdapter()->quoteInto('path = ?', $path))
               ->where($this->getAdapter()->quoteInto('site_id IN(?)', 
                   array_unique($siteIds))
        );
        return $this->getAdapter()->fetchPairs($select->__toString());
    }
    
    /**
     * Update config value
     * 
     * @param array $data
     * @return bool
     */
    public function save($data)
    {
        if (!$row = $this->fetchRow($this->_prefix . 'core_config_value' . '.`path` = "' . $data['path'] . '"')) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    "Config field '%s' was not found", $data['path']
            ));
            return false;
        }
        $row->value = $data['value'];
        $row->site_id = isset($data['site_id']) ? $data['site_id'] : 0;
        $row->save();
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return true;
    }
    
    /**
     * Removes config values, and all of it childrens
     * 
     * @param string $path
     * @return Axis_Core_Model_Config_Value Provides fluent intarface
     */
    public function remove($path)
    {
        $this->delete("path LIKE '{$path}%'");
        return $this;
    }
}