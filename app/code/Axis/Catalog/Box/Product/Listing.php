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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Box_Product_Listing extends Axis_Core_Box_Abstract
{
    protected $_productsCount = 5;
    protected $_columnsCount = 3;

    /**
     * @return integer
     */
    public function getProductsCount()
    {
        if (null === $this->productsCount) {
            return $this->_productsCount;
        }
        return $this->productsCount;
    }

    /**
     * @return integer
     */
    public function getColumnsCount()
    {
        if (null === $this->columnsCount) {
            return $this->_columnsCount;
        }
        return $this->columnsCount;
    }

    /**
     * @return boolean
     */
    public function hasContent()
    {
        return (bool) count($this->products);
    }
}