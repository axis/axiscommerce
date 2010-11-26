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
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Model_Review_Row extends Axis_Db_Table_Row
{
    /**
     * Adds marks to review
     * 
     * @param array $marks rating_id => mark
     * @return Axis_Community_Model_Review_Row provides fluent interface
     */
    public function saveMark(array $marks)
    {
        foreach ($marks as $rating_id => $mark) {
            if (!$row = Axis::single('community/review_mark')->find($this->id, $rating_id)->current()) {
                $row = Axis::single('community/review_mark')->createRow();
            }
            $row->setFromArray(array(
                'review_id' => $this->id,
                'rating_id' => $rating_id,
                'mark' => $mark
            ));
            $row->save();
        }
        return $this;
    }
}