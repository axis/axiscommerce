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
 * @subpackage  Axis_Community_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Box_ReviewRating extends Axis_Core_Box_Abstract
{
    protected $_title = 'Review rating';
    protected $_class = 'box-review-rating-product';
    protected $_disableWrapper = true;

    public function init()
    {
        $this->review_count = array();
        $this->ratings = array();
        return true;
    }

    public function initData()
    {
        if (!$this->hasData('product_id')) {
            return true;
        }
        if (!is_array($this->review_count)) {
            return true;
        }
        /* if review already loaded */
        if (in_array($this->product_id, array_keys($this->review_count))) {
            return true;
        }

        if (!is_array($this->product_ids)) {
            $this->setProductIds(array($this->product_id));
        } elseif (!in_array($this->product_id, $this->getProductIds())) {
            $productIds = $this->product_ids;
            $productIds[] = $this->product_id;
            $this->setProductIds($productIds);
        }
        $productIds = array_diff(
            $this->getProductIds(), array_keys($this->review_count)
        );

        $modelCommunityReview = Axis::single('community/review');
        $this->review_count += $modelCommunityReview->cache()
            ->getCountByProductId($productIds);

        $this->ratings += $modelCommunityReview->cache()
            ->getAverageProductRating(
                $productIds,
                $this->getView()->config('community/review/merge_average')
            );
    }
}