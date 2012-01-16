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
class Axis_Catalog_Model_Category_Row extends Axis_Db_Table_Row
{
    /**
     * Retrieve the set of parent categories
     *
     * @param int $categoryId
     * @param int $languageId [optional]
     * @return array Keys are category ids
     */
    public function getParentItems($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        $select = $this->select()
            ->from('catalog_category', array())
            ->join('catalog_category',
                'cc.lft BETWEEN cc2.lft AND cc2.rgt',
                '*')
            ->join('catalog_hurl',
                'ch.key_id = cc2.id',
                array('key_word'))
            ->joinLeft('catalog_category_description',
                'ccd.category_id = cc2.id AND ccd.language_id = ' . $languageId,
                array('name', 'meta_title', 'meta_description', 'meta_keyword'))
            ->where('cc.id = ?', $this->id)
            ->where('cc2.site_id = cc.site_id')
            ->where('ch.key_type = "c"')
            ->order('cc2.lft');

        return $select->fetchAssoc();
    }

    /**
     * Retrieve the set of child categories
     *
     * @param bool $deep [optional]
     * @param bool $activeOnly [optional]
     * @param integer $languageId [optional]
     * @return array
     */
    public function getChildItems($deep = false, $activeOnly = false, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        $select = $this->select()
            ->from('catalog_category')
            ->joinLeft('catalog_category_description',
                'ccd.category_id = cc.id AND ccd.language_id = ' . $languageId,
                'name')
            ->joinLeft('catalog_hurl',
                "cc.id = ch.key_id AND ch.key_type = 'c'",
                'key_word')
            ->where("cc.lft BETWEEN {$this->lft} AND {$this->rgt}")
            ->where("cc.site_id = {$this->site_id}")
            ->order('cc.lft');

        if (!$deep) {
            $select->where("cc.lvl = " . ($this->lvl + 1));
        }

        if ($activeOnly) {
            $select->where("cc.status = 'enabled'");
        }

        return $select->fetchAll();
    }

    /**
     * @param boolean $recursive [optional] If true - items of subcategories will be counted also
     * @return integer
     */
    public function getProductsCount($recursive = false)
    {
        $select = $this->select();
        if ($recursive) {
            $select->from('catalog_product_category', 'COUNT(DISTINCT cpc.product_id)')
                ->join('catalog_category', 'cc.id = cpc.category_id')
                ->where("cc.lft BETWEEN {$this->lft} AND {$this->rgt}")
                ->where("cc.rgt BETWEEN {$this->lft} AND {$this->rgt}");
        } else {
            $select->from('catalog_product_category', 'COUNT(*)')
                ->where('cpc.category_id = ?', $this->id);
        }
        return $select->fetchOne();
    }
}