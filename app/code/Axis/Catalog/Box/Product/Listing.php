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
    protected $_viewMode        = 'grid';
    protected $_productsCount   = 6;
    protected $_columnsCount    = 3;

    /**
     * @return integer
     */
    public function getProductsCount()
    {
        if (null === $this->products_count) {
            return $this->_productsCount;
        }
        return $this->products_count;
    }

    /**
     * @return integer
     */
    public function getColumnsCount()
    {
        if (null === $this->columns_count) {
            return $this->_columnsCount;
        }
        return $this->columns_count;
    }

    public function getConfigurationFields()
    {
        return array(
            'view_mode' => array(
                'fieldLabel'    => Axis::translate('catalog')->__('View mode'),
                'initialValue'  => $this->_viewMode,
                'data'          => array(
                    'grid' => Axis::translate('catalog')->__('Grid'),
                    'list' => Axis::translate('catalog')->__('List')
                )
            ),
            'products_count' => array(
                'fieldLabel'    => Axis::translate('catalog')->__('Products Count'),
                'initialValue'  => $this->_productsCount
            ),
            'columns_count' => array(
                'fieldLabel'    => Axis::translate('catalog')->__('Columns Count'),
                'initialValue'  => $this->_columnsCount
            )
        );
    }
}