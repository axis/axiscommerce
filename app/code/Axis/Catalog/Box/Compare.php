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
class Axis_Catalog_Box_Compare extends Axis_Core_Box_Abstract
{
    protected $_title = 'Compare';
    protected $_class = 'box-compare';

    public function init()
    {
        $modelCompare = Axis::single('catalog/product_compare');
        $ids = $modelCompare->getItems();

        if (!count($ids)) {
            return false;
        }

        $products = Axis::single('catalog/product')->select('*')
            ->addCommonFields()
            ->where('cp.id IN (?)', $ids)
            ->fetchProducts($ids);

        // if compare session has old product ids that are not in the db now
        foreach (array_diff($ids, array_keys($products)) as $id) {
            $modelCompare->remove($id);
        }

        if (!$modelCompare->hasItems()) {
            return false;
        }

        $this->items = $products;
        return true;
    }

    protected function _beforeRender()
    {
        return $this->hasItems();
    }
}