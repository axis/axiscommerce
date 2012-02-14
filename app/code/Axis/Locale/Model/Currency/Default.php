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
 * @package     Axis_Locale
 * @subpackage  Axis_Locale_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Locale
 * @subpackage  Axis_Locale_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_Model_Currency_Default extends Axis_Locale_Model_Currency implements  Axis_Config_Handler_Interface
{
    /**
     *
     * @static
     * @param string $value
     * @return string
     */
    public static function prepareConfigOptionValue($value)
    {
        $row = Axis::single('locale/currency')->select()
            ->where('code = ?' , $value)
            ->fetchRow();
        if ($row instanceof Axis_Db_Table_Row && 1 !== $row->rate) {
            $row->rate = 1;
            $row->save();
            Axis::message()->addNotice(Axis::translate('locale')->__(
                'Currency rate was changed to 1.00'
            ));
        }

        return $value;
    }

    /**
     *
     * @param string $value
     * @param Zend_View_Interface $view
     * @return string
     */
    public static function getHtml($value, Zend_View_Interface $view = null)
    {
        return $view->formSelect('confValue',
            $value, null, self::getConfigOptionsArray()
        );
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public static function getConfigOptionValue($value)
    {
        return $value;
    }
}