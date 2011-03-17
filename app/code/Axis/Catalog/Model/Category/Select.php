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
class Axis_Catalog_Model_Category_Select extends Axis_Db_Table_Select
{

    /**
     * @param int $languageId
     * @return Axis_Catalog_Model_Category_Select
     */
    public function addName($languageId = null)
    {
        if (null === $languageId) {
            $cols = array('ccd.language_id', 'ccd.name');
        } else {
            $cols = 'ccd.name';
        }
        return $this->addDescriptionTable($languageId)->columns($cols);
    }

    /**
     * @param int $languageId
     * @return Axis_Catalog_Model_Category_Select
     */
    public function addDescriptionTable($languageId = null)
    {
        if (array_key_exists('ccd', $this->_parts[self::FROM])) {
            return $this;
        }
        if (null !== $languageId) {
            return $this->joinLeft(
                'catalog_category_description',
                'ccd.category_id = cc.id AND ccd.language_id = :languageId'
            )->bind(array('languageId' => $languageId));
        }

        return $this->joinLeft(
            'catalog_category_description',
            'ccd.category_id = cc.id'
        );
    }

    /**
     * @return Axis_Catalog_Model_Category_Select
     */
    public function addKeyWord()
    {
        return $this->addHurlTable()->columns('ch.key_word');
    }

    /**
     * @return Axis_Catalog_Model_Category_Select
     */
    public function addHurlTable()
    {
        if (array_key_exists('ch', $this->_parts[self::FROM])) {
            return $this;
        }

        return $this->joinLeft(
            'catalog_hurl',
            'ch.key_id = cc.id'
        )->where("ch.key_type='c'");
    }

    /**
     *
     * @param mixed array|int $siteId
     * @return Axis_Catalog_Model_Category_Select
     */
    public function addSiteFilter($siteId)
    {
        if (null === $siteId) {
            return $this;
        }
        if (!is_array($siteId)) {
            $siteId = array($siteId);
        }
        return $this->where('cc.site_id IN(?)', $siteId);
    }

    /**
     *
     * @return Axis_Catalog_Model_Category_Select
     */
    public function addDisabledFilter()
    {
        $disabledCategories = $this->getTable()->getDisabledIds();
        return $this->where('cc.id NOT IN (?)', $disabledCategories);
    }


    /**
     *
     * @param string $column
     * @return array
     */
    public function fetchAllAndSortByColumn($column)
    {
        $dataset = array();
        foreach ($this->fetchAll() as $_row) {
            $dataset[$_row[$column]][] = $_row;
        }
        return $dataset;
    }
}