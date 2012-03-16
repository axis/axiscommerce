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
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tag_Box_Product extends Axis_Catalog_Box_Product_Abstract
{
    protected $_title = 'Tags';
    protected $_class = 'box-tag';

    protected function _construct()
    {
        $this->setData('cache_lifetime', 0);
    }

    protected function _beforeRender()
    {
        if (!$this->product_id = $this->_getProductId()) {
            return false;
        }

        if ($this->product_id == $this->last_product_id && $this->hasTags()) {
            return true;
        }

        $this->last_product_id = $this->product_id;
        $this->tags = Axis::single('tag/customer')->getByProductId(
            $this->product_id
        );
    }

    protected function _getCacheKeyParams()
    {
        return array(
            $this->_getProductId()
        );
    }
}
