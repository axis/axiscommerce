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
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Discount_Model_Discount_Select extends Axis_Db_Table_Select
{
    /**
     * Only special discounts will be returned with this type of filter
     */
    public function addFilterBySpecial()
    {
        $specialSelect = Axis::model('discount/eav')->select('discount_id')
            ->where('de.entity = ?', 'special')
            ->where('de.value = ?', 1);

        return $this->where("d.id = ANY ?", $specialSelect);
    }

    /**
     * Only non-special discounts will be returned with this type of filter
     */
    public function addFilterByNonSpecial()
    {
        $specialSelect = Axis::model('discount/eav')->select('discount_id')
            ->where('de.entity = ?', 'special')
            ->where('de.value = ?', 1);

        return $this->where("d.id <> ALL ?", $specialSelect);
    }
}