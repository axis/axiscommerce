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
class Axis_Catalog_Model_Category extends Axis_Db_Table
{
    protected $_name = 'catalog_category';

    protected $_primary = 'id';

    protected $_rowClass = 'Axis_Catalog_Model_Category_Row';

    protected $_dependentTables = array(
        'Axis_Catalog_Model_Category_Description'
    );

    /**
     * Inactive category ids. Including
     * active child categories of inactive parents
     *
     * @var array
     */
    private $_disabledCategories = null;

    /**
     *
     * @param array $data
     * @param int $parentId
     * @return int
     */
    public function insertItem(array $data, $parentId = 0)
    {
        $parentRow = $this->fetchRow(
            $this->getAdapter()->quoteInto('id = ?', $parentId)
        );

        if (!$parentRow)  {
            return false;
        }
        $siteId = $parentRow->site_id;

        $this->update(
            array('lft' => new Zend_Db_Expr('lft + 2')),
            array(
                'site_id = ' . $siteId,
                'lft > ' . $parentRow->rgt,
            )
        );
        $this->update(
            array('rgt' => new Zend_Db_Expr('rgt + 2')),
            array(
                'site_id = ' . $siteId,
                'rgt >= ' . $parentRow->rgt
            )
        );

        $data['site_id'] = $siteId;
        $data['lvl'] = $parentRow->lvl + 1;
        $data['lft'] = $parentRow->rgt;
        $data['rgt'] = $parentRow->rgt + 1;
        return $this->insert($data);
    }

    /**
     *
     * @param int $id
     * @return Zend_Db_Statement_Interface
     */
    public function deleteItem($id)
    {
        $db = $this->getAdapter();
        $row = $this->fetchRow($db->quoteInto('id = ?', $id));
        if (!$row) {
            return false;
        }
        /*
         * select cid array
         * delete from this where id in cid array
         * delete from description where cat_id in cid array
         * update tree index
         */

        $idForDelete = $this->select('id')
            ->where("lft BETWEEN $row->lft AND $row->rgt")
            ->where('site_id = ?', $row->site_id)
            ->fetchCol()
            ;

        if (!sizeof($idForDelete)) {
            return false;
        }

        $db->delete(
            $this->_prefix . 'catalog_category_description',
            $db->quoteInto('category_id IN(?)', $idForDelete)
        );
        $db->delete(
            $this->_prefix . 'catalog_product_category',
            $db->quoteInto('category_id IN(?)', $idForDelete)
        );
        Axis::single('catalog/hurl')->delete(array(
            $db->quoteInto('key_id IN(?)', $idForDelete),
            "key_type = 'c'"
        ));

        $nstreeTable = new Axis_NSTree_Table();
        return $nstreeTable->deleteNode($id, true);
    }

    /**
     * @param int $languageId
     * @param mixed(array) $siteIds
     * @param bool $isActive
     * @return array
     * <pre>
     * [site_id => array, site_id => array]
     * </pre>
     */
    public function getFlatTree($languageId, $siteIds = null, $activeOnly = false)
    {
        $select = $this->select(
                array('id', 'site_id', 'lvl', 'lft', 'rgt')
            )
            ->joinLeft(
                'catalog_category_description',
                'ccd.category_id = cc.id AND ccd.language_id = :languageId',
                'name'
            )
            ->joinLeft(
                'catalog_hurl',
                'ch.key_id = cc.id',
                'key_word'
            )
            ->where("ch.key_type='c'")
            ->order('cc.lft')
            ->bind(array('languageId' => $languageId))
            ;

        if (null !== $siteIds) {
            $select->where('cc.site_id IN(?)', $siteIds);
        }

        if ($activeOnly && $disabledCategories = $this->getDisabledIds()) {
            $select->where('cc.id NOT IN (?)', $disabledCategories);
        }

        $select = $select->query();
        $tree = array();

        while (($row = $select->fetch())) {
            $tree[$row['site_id']][] = $row;
        }

        return $tree;
    }

    /**
     * Retrive categories info with data to build nested tree
     * Used at the backend, so it returns disabled categories also
     *
     * @return array Each element is a separate category
     */
    public function getNestedTreeData()
    {
        return $this->select('*')
            ->joinLeft(
                'catalog_category_description',
                'cc.id = ccd.category_id AND ccd.language_id = ?',
                '*'
            )
            ->order('cc.lft')
            ->order('cc.site_id')
            ->fetchAll(Axis_Locale::getLanguageId())
            ;
    }

    /**
     *
     * @param int $siteId
     * @return array
     */
    public function getSiteCategories($siteId)
    {
        return $this->select('id')
            ->where('site_id = ? ', $siteId)
            ->fetchCol();
    }

    /**
     *
     * @param int $categoryId
     * @return mixed
     */
    public function getInfoWithKeyWord($categoryId)
    {
        if (!$categoryId) {
            return false;
        }
        return $this->select('*')
            ->joinLeft(
                'catalog_hurl',
                "ch.key_id = cc.id AND ch.key_type = 'c'",
                'key_word'
            )
            ->where('cc.id = ?', $categoryId)
            ->fetchRow();
    }

    /**
     * @param string $url
     * @param int $siteId [optional]
     * @return Axis_Catalog_Model_Category_Row
     */
    public function getByUrl($url, $siteId = null)
    {
        if (null === $siteId) {
            $siteId = Axis::getSiteId();
        }
        return $this->fetchRow($this->select('*')
            ->joinInner(
                'catalog_hurl',
                "ch.key_id = cc.id AND ch.key_type = 'c'"
            )
            ->where('ch.key_word = ?', $url)
            ->where('ch.site_id = ?', $siteId)
        );
    }

    /**
     * Retrieve root category of site
     *
     * @param int $siteId
     * @return Axis_Db_Table_Row
     */
    public function getRoot($siteId = null)
    {
        if (null === $siteId) {
            $siteId = Axis::getSiteId();
        }
        return $this->fetchRow(
            $this->select()
                ->where('site_id = ?', $siteId)
                ->where('lvl = 0')
        );
    }

    public function getRootCategories()
    {
        return $this->select()
            ->from('catalog_category')
            ->joinLeft('catalog_category_description',
                'ccd.category_id = cc.id AND ccd.language_id = ?',
                'name')
            ->where('lvl = 0')
            ->order('cc.id ASC')
            ->fetchAll(Axis_Locale::getLanguageId());
    }

    /**
     * Retrieve disabled category ids including
     * enabled childs of disabled category
     *
     * @return array
     */
    public function getDisabledIds()
    {
        if (null === $this->_disabledCategories) {
            $categories = $this->fetchAll(
                "status = 'enabled' AND site_id = " . Axis::getSiteId(), 'lft'
            );
            $disabledCategories = $this->fetchAll(
                "status = 'disabled' AND site_id = " . Axis::getSiteId(), 'lft'
            );

            $result = array();
            foreach ($disabledCategories as $disabledCategory) {
                $result[$disabledCategory['id']] = $disabledCategory['id'];
                foreach ($categories as $category) {
                    if ($category['lft'] > $disabledCategory['lft']
                        && $category['rgt'] < $disabledCategory['rgt']) {

                        $result[$category['id']] = $category['id'];
                    }
                }
            }
            $this->_disabledCategories = array_values($result);
        }
        return $this->_disabledCategories;
    }

    /**
     * Retrieve one-dimensional array of categories where the product lies in,
     * including their parent categories
     *
     * @param int $productId
     * @param int $languageId   [optional]
     * @param int $siteId       [optional]
     * @return array
     */
    public function getRelatedCategoriesByProductId($productId, $languageId = null, $siteId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        if (null === $siteId) {
            $siteId = Axis::getSiteId();
        }

        return $this->select()
            ->from(array('cc' => 'catalog_category'))
            ->joinLeft(array('cc1' => 'catalog_category'),
                'cc.lft BETWEEN cc1.lft AND cc1.rgt',
                '*'
            )
            ->joinInner(
                'catalog_product_category',
                'cc.id = cpc.category_id'
            )
            ->joinInner(
                'catalog_hurl',
                'ch.key_id = cc1.id',
                'key_word'
            )
            ->joinInner(
                'catalog_category_description',
                'ccd.category_id = cc1.id',
                array('id' => 'category_id', 'name', 'meta_title', 'meta_description', 'meta_keyword'))
            ->where('cpc.product_id = ?', $productId)
            ->where('cc.site_id = ?', $siteId)
            ->where('cc1.site_id = ?', $siteId)
            ->where('ccd.language_id = ?', $languageId)
            ->where("ch.key_type = 'c'")
            ->order(array('cpc.category_id', 'cpc.product_id', 'cc1.lvl'))
            ->fetchAll();
    }
}