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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Category_Select extends Axis_Db_Table_Select
{
    /**
     * 
     * @return Axis_Catalog_Model_Product_Category_Select
     */
    public function addHurlTable()
    {
        if (array_key_exists('ch', $this->_parts[self::FROM])) {
            return $this;
        }

        return $this->joinLeft(
            'catalog_hurl',
            "ch.key_id = cp.id AND ch.key_type = 'p'"
        );
    }

    /**
     *
     * @return Axis_Catalog_Model_Product_Category_Select
     */
    public function addKeyWord()
    {
        return $this->addHurlTable()->columns('ch.key_word');
    }

    /**
     *
     * @param mixed array|int $siteId
     * @return Axis_Catalog_Model_Product_Category_Select
     */
    public function addSiteFilter($siteId)
    {
        if (null === $siteId) {
            return $this;
        }
        if (!is_array($siteId)) {
            $siteId = array($siteId);
        }
        return $this->joinLeft('catalog_category',
            'cpc.category_id = cc.id'
        )->where('cc.site_id IN(?)', $siteId);
    }


    /**
     * 
     * @return Axis_Catalog_Model_Product_Category_Select
     */
    public function addProductTable()
    {
        if (array_key_exists('cp', $this->_parts[self::FROM])) {
            return $this;
        }

        return $this->joinLeft('catalog_product', 'cp.id = cpc.product_id');
    }

    /**
     *
     * @param bool $status
     * @return Axis_Catalog_Model_Product_Category_Select
     */
    public function addActiveFilter($status = true)
    {
        return $this->addProductTable()->where('cp.is_active = ?', (bool) $status);
    }

    /**
     *
     * @param Axis_Date $date
     * @return Axis_Catalog_Model_Product_Category_Select
     */
    public function addDateAvailableFilter(Axis_Date $date = null)
    {
        if (null === $date) {
            $date = Axis_Date::now();
        }
        return $this->addProductTable()->where(
            'cp.date_available IS NULL OR cp.date_available <= ?',
            $date->toPhpString('Y-m-d')
        );
    }

    /**
     * @param int $languageId
     * @return Axis_Catalog_Model_Category_Select
     */
    public function addName($languageId = null)
    {
        $this->addDescriptionTable();
        if (null === $languageId) {
            $this->columns(array('cpd.language_id', 'cpd.name'));
        } else {
            $this->columns('cpd.name')->where(
                'cpd.language_id = ?', $languageId
            );
        }
        return $this;
    }

    /**
     *
     * @return Axis_Catalog_Model_Category_Select
     */
    public function addDescriptionTable()
    {
        if (array_key_exists('cpd', $this->_parts[self::FROM])) {
            return $this;
        }

        return $this->joinLeft('catalog_product_description',
            'cpd.product_id = cpc.product_id'
        );
    }

}