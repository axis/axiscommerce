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
 * @copyright   Copyright 2008-2011 Axis
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

    protected $_selectClass = 'Axis_Catalog_Model_Category_Select';

    protected $_dependentTables = array(
        'Axis_Catalog_Model_Category_Description'
    );

    /**
     *
     * @param array $data
     * @param int $parentId
     * @return int
     */
    public function insertItem(array $data, $parentId = 0)
    {
        $parentRow = $this->find($parentId)->current();

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
        $row = $this->find($id)->current();
        if (!$row) {
            return false;
        }
        /*
         * select cid array
         * delete from this where id in cid array
         * delete from description where cat_id in cid array
         * update tree index
         */

        $childrenIds = $this->select('id')
            ->where("lft BETWEEN $row->lft AND $row->rgt")
            ->where('site_id = ?', $row->site_id)
            ->fetchCol()
            ;

        if (!sizeof($childrenIds)) {
            return false;
        }

        Axis::single('catalog/hurl')->delete(array(
            $this->getAdapter()->quoteInto('key_id IN(?)', $childrenIds),
            "key_type = 'c'"
        ));

        $nstreeTable = new Axis_NSTree_Table();
        return $nstreeTable->deleteNode($id, true);
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
     * @param string $url
     * @param int $siteId [optional]
     * @return Axis_Catalog_Model_Category_Row
     */
    public function getByUrl($url, $siteId = null)
    {
        if (null === $siteId) {
            $siteId = Axis::getSiteId();
        }
        return $this->select('*')
            ->joinInner(
                'catalog_hurl',
                "ch.key_id = cc.id AND ch.key_type = 'c'"
            )
            ->where('ch.key_word = ?', $url)
            ->where('ch.site_id = ?', $siteId)
            ->fetchRow()
        ;
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
        return $this->select()
            ->where('site_id = ?', $siteId)
            ->where('lvl = 0')
            ->fetchRow()
        ;
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
        if (!Zend_Registry::isRegistered('catalog/disabled_categories')) {
            $enableds  = $this->select('*')->order('lft')
                ->where("status = 'enabled'")
                ->fetchAll();
            $disableds = $this->select('*')->order('lft')
                ->where("status = 'disabled'")
                ->fetchAll();

            $result = array();
            foreach ($disableds as $_disabled) {
                $result[$_disabled['id']] = $_disabled['id'];
                foreach ($enableds as $_enabled) {
                    if ($_enabled['lft'] > $_disabled['lft']
                        && $_enabled['rgt'] < $_disabled['rgt']) {

                        $result[$_enabled['id']] = $_enabled['id'];
                    }
                }
            }
            Zend_Registry::set('catalog/disabled_categories', array_values($result));
        }
        return Zend_Registry::get('catalog/disabled_categories');
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
    
    /**
     *
     * @param Axis_Db_Table_Row $site
     * @return Axis_Db_Table_Row 
     */
    public function addNewRootCategory(Axis_Db_Table_Row $site) 
    {
        $modelDescription = Axis::model('catalog/category_description');
        $languageIds      = array_keys(Axis_Locale_Model_Language::collect());
        $timestamp        = Axis_Date::now()->toSQLString();
        $row = $this->createRow(array(
            'site_id'     => $site->id,
            'lft'         => 1,
            'rgt'         => 2,
            'lvl'         => 0,
            'created_on'  => $timestamp,
            'modified_on' => $timestamp,
            'status'      => 'enabled'
        ));
        $row->save();
        
        foreach ($languageIds as $languageId) {
            $modelDescription->createRow(array(
                'category_id' => $row->id,
                'language_id' => $languageId,
                'name'        => $site->name,
                'description' => 'Root Category'
            ))->save();
        }
        
        return $row;
    }
}