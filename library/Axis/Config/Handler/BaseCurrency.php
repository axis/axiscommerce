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
 * @package     Axis_Config
 * @subpackage  Axis_Config_Handler
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Config
 * @subpackage  Axis_Config_Handler
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Config_Handler_BaseCurrency implements  Axis_Config_Handler_Interface
{
    /**
     *
     * @static
     * @param string $value
     * @return string
     */
    public static function getSaveValue($value)
    {
        $row = Axis::single('locale/currency')->select()
            ->where('code = ?' , $value)
            ->fetchRow3();
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
            $value, null, Axis_Collect_Currency::collect()
        );
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public static function getConfig($value)
    {
        return $value;
    }
}
