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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Import_Profile extends Axis_Db_Table
{
    protected $_name = 'import_profile';

    public function getList()
    {
        return $this->select()
            ->order('updated_at DESC, created_at DESC')
            ->fetchAll();
    }

    public function save($data)
    {
        if ($data['id'] == '') {
            $data['updated_at'] = $data['created_at'] = Axis_Date::now()->toSQLString();
            unset($data['id']);
            $row = $this->createRow();
        } else {
            $data['updated_at'] = Axis_Date::now()->toSQLString();
            $row = $this->find($data['id'])->current();
        }
        $row->setFromArray($data);
        if ($result = $row->save()) {
            Axis::message()->addSuccess(
                Axis::translate('admin')->__(
                    'Profile was saved successfully'
                )
            );
        };
        return $result;
    }

    public function delete($data)
    {
        $where = $this->getAdapter()->quoteInto('id IN(?)', $data);

        if ($result = parent::delete($where)) {
            Axis::message()->addSuccess(
                Axis::translate('admin')->__(
                    'Profile was deleted successfully'
                )
            );
        };
        return $result;
    }
}