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
class Axis_Catalog_Model_Product_Manufacturer_Select extends Axis_Db_Table_Select
{
    /**
     * Add description table to select
     *
     * @param mixed $languageId If false - description will join all available languages
     * @return Axis_Catalog_Model_Product_Manufacturer_Select
     */
    public function addDescription($languageId = null)
    {
        if (false === $languageId) {
            $joinOn = 'cpm.id = cpmt.manufacturer_id';
        } else {
            if (null === $languageId) {
                $languageId = Axis_Locale::getLanguageId();
            }
            $joinOn = 'cpm.id = cpmt.manufacturer_id AND language_id = ' . $languageId;
        }

        return $this->joinLeft('catalog_product_manufacturer_title', $joinOn, '*');
    }


    /**
     * Add url to select
     *
     * @return Axis_Catalog_Model_Product_Manufacturer_Select
     */
    public function addUrl()
    {
        return $this->joinLeft('catalog_hurl',
            "ch.key_type = 'm' AND ch.key_id = cpm.id",
            array('url' => 'key_word')
        );
    }

    /**
     * Join description table to select
     *
     * @return Axis_Catalog_Model_Product_Manufacturer_Select
     */
    public function joinDescription()
    {
        return $this->joinLeft('catalog_product_manufacturer_title',
            'cpm.id = cpmt.manufacturer_id',
            array()
        );
    }
}