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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Option_Mail_Boxes implements Axis_Config_Option_Array_Interface
{
    /**
     *
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        $rows = Axis::single('core/config_value')
            ->select(array('path', 'value'))
                ->where('path LIKE "mail/mailboxes/%"')
                ->fetchPairs();

        $result = array();
        foreach ($rows as $rowId => $rowValue) {
            $result[substr($rowId, 15)] = $rowValue;
        }
        return $result;
    }

    /**
     *
     * @static
     * @param int $key
     * @return string
     */
    public static function getConfigOptionValue($key)
    {
        return Axis::single('core/config_value')
            ->select('value')
            ->where('path LIKE "mail/mailboxes/%"')
            ->where('SUBSTR(path,16) = ?', $key)
            ->fetchOne()
            ;
    }
}