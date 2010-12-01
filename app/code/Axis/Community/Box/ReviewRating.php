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
        $this->reviewCount = array();
        $this->ratings = array();
    }

    public function initData()
    {
        if (!$this->hasData('productId')) {
            return true;
        }
        if (!is_array($this->reviewCount)) {
            return true;
        }
        /* if review already loaded */
        if (in_array($this->productId, array_keys($this->reviewCount))) {
            return true;
        }

        if (!is_array($this->productIds)) {
            $this->productIds= array($this->productId);
        } elseif (!in_array($this->productId, $this->productIds)) {
            $productIds = $this->productIds;
            $productIds[] = $this->productId;
            $this->productIds = $productIds;
        }

        $productIds = array_diff($this->productIds, array_keys($this->reviewCount));

        $this->reviewCount +=
            Axis::single('community/review')->cache()->getCountByProductId($productIds);

        $this->ratings +=
            Axis::single('community/review')->cache()->getAverageProductRating(
                $productIds,
                self::$view->config('community/review/merge_average')
            );
    }
}