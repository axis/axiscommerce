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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Model_Review_Mark extends Axis_Db_Table
{
    protected $_name = 'community_review_mark';
    protected $_primary = array('review_id', 'rating_id');
    
    /**
     * Checks is customer has been voted already for product
     * 
     * @param int $customerId
     * @param int $productId
     * @param int $reviewId review that will be excluded from query.
     *  This makes possible to change already posted review marks.
     * @return bool
     */
    public function isCustomerVoted($customerId, $productId, $reviewId = null)
    {
        if (!is_numeric($customerId)) { // guests do not have permission to vote
            return true;
        }
        
        $select = $this->select('id')
            ->join('community_review', 'crm.review_id = cr.id')
            ->where('cr.product_id = ?', $productId)
            ->where('cr.customer_id = ?', $customerId);
        
        if (null !== $reviewId) {
            $select->where('cr.id <> ?', $reviewId);
        }
        return $select->count() ? true : false;
    }
    
}