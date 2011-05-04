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
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Config_Value extends Axis_Db_Table
{
    protected $_name = 'core_config_value';

    /**
     *
     * @param string $path
     * @param int $siteId
     * @return mixed
     */
    public function getValue($path, $siteId)
    {
        $row = $this->select()
            ->where('path = ?', $path)
            ->where('site_id IN(?)', array(0, $siteId))
            ->order('site_id DESC')
            ->fetchRow();
        if ($row) {
            return $row->value;
        }
        return '';
    }

    /**
     * Update config value
     * 
     * @param array $data
     * @return bool
     */
    public function save($data)
    {
        $row = $this->select()
            ->where('path = ?', $data['path'])
            ->fetchRow();
        
        if (!$row) {
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