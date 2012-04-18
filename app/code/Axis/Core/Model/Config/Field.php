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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Config_Field extends Axis_Db_Table
{
    protected $_name = 'core_config_field';

    protected $_primary = 'id';

    protected $_rowClass = 'Axis_Core_Model_Config_Field_Row';

    protected $_selectClass = 'Axis_Core_Model_Config_Field_Select';

    /**
     * Insert or update config field
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->select()
            ->where('path = ?', $data['path'])
            ->fetchRow();

        if (!$row) {
            $row = $this->createRow();
        } 
        $row->setFromArray($data);
        
        $row->lvl = count(explode('/', $row->path));
        
        if ($row->lvl <= 2) {
            $row->type = '';
        }
        
        $row->save();
        
        return $row;
    }
}