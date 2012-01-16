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
 * @copyright   Copyright 2008-2012 Axis
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
     * @param array $ratings rating_id => mark
     * @return Axis_Community_Model_Review_Row provides fluent interface
     */
    public function setRating(array $ratings) 
    {
        $model = Axis::model('community/review_mark');
        
        foreach ($ratings as $ratingId => $mark) {
                
            if (empty($this->customer_id)) {
                Axis::message()->addNotice(
                    Axis::translate('community')->__(
                        'Guests do not have the permission to vote. Review was saved without ratings'
                ));
                break;
            }

            if ($model->isCustomerVoted(
                $this->customer_id, $this->product_id, $this->id
            )) {
                Axis::message()->addNotice(
                    Axis::translate('community')->__(
                        'You have already voted for this product. Review was saved without ratings'
                ));
                break;
            }

            $row = $model->getRow(array(
                'review_id' => $this->id,
                'rating_id' => $ratingId,
                'mark'      => $mark
            ));
            $row->save();
        }
        return $this;
    }
}