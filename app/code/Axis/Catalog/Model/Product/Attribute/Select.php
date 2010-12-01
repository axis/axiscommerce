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
 * @subpackage  Axis_Catalog_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Attribute_Select extends Axis_Db_Table_Select
{

//    /**
//     * @return Axis_Catalog_Model_Product_Attribute_Select
//     */
//    public function addOption()
//    {
//        return $this->addOptionTable()->columns('cpo.*');
//    }
//
//    /**
//     * @return Axis_Catalog_Model_Product_Attribute_Select
//     */
//    public function addOptionTable()
//    {
//        if (array_key_exists('cpo', $this->_parts[self::FROM])) {
//            return $this;
//        }
//        return $this->joinInner(
//            'catalog_product_option',
//            'cpo.id = cpa.option_id'
//        );
//    }
//
//    /**
//     * @return Axis_Catalog_Model_Product_Attribute_Select
//     */
//    public function addOptionVisible()
//    {
//        $this->addOptionTable();
//        return $this->columns('cpo.visible');
//    }
}