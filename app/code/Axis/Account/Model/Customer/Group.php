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
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_Group extends Axis_Db_Table
{
    const GROUP_ALL_ID = 0;
    const GROUP_GUEST_ID = 5;

    protected $_name = 'account_customer_group';
    protected $_primary = 'id';

    /**
     *
     * @param array $data
     * @return bool
     */
    public function save($data)
    {
        if (!isset($data['id'])
            || !$row = $this->find($data['id'])->current()) {

            unset($data['id']);
            $row = $this->createRow();
            $oldData = null;
        } else {
            if (self::GROUP_GUEST_ID === $data['id']
                && self::GROUP_ALL_ID === $data['id']) {
                // disallow to change system groups
                return;
            }
            $oldData = $row->toArray();
        }

        $row->setFromArray($data)
            ->save();

        Axis::dispatch('account_group_save_after', array(
            'old_data'  => $oldData,
            'group'     => $row
        ));
        return true;
    }
}