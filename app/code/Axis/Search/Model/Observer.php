<?php
/**
 * Axis
 *
 * @copyright Copyright 2008-2011 Axis
 * @license GNU Public License V3.0
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
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Search_Model_Observer
{
    public function updateSearchIndexOnProductSave(array $data)
    {
        $product = $data['product'];
        $indexer = Axis::model('search/indexer');

        $select = Axis::model('catalog/product')->select(array('id', 'sku'))
            ->join('catalog_product_description',
                'cpd.product_id = cp.id',
                array('name', 'description')
            )->joinRight('locale_language',
                'll.id = cpd.language_id',
                'locale'
            )->joinLeft('catalog_product_image',
                'cpi.id = cp.image_thumbnail',
                array('image_thumbnail' => 'path')
            )->joinLeft('catalog_product_image_title',
                'cpit.image_id = cpi.id',
                array('image_title' => 'title')
            )->join('catalog_product_category', 'cp.id = cpc.product_id')
            ->join('catalog_category',
                'cc.id = cpc.category_id',
                'site_id'
            )->joinLeft('catalog_hurl',
                "ch.key_id = cp.id AND ch.key_type='p'",
                'key_word'
            )
            ->order('cc.site_id')
            ->order('cpd.language_id')
            ->group(array('cc.site_id', 'cpd.language_id', 'cp.id'))
            ->where('cp.id = ?', $product->id)
            ;
        $rowset = $select->fetchRowset();
        $index  = $path = null;

        foreach ($rowset as $row) {
            $_path = $indexer->getIndexPath(
                $row->site_id  . '/' . $row->locale
            );
            //next index
            if ($path !== $_path) {
                //save prev index
                if ($index) {
                    $index->optimize();
                    $index->commit();
                }
                $path = $_path;
                $index = $indexer->getIndex($path);
            }
            $hits = $index->find("sku:$row->sku");
            if (count($hits)) {
                $index->delete(current($hits));
            }
        }
        if ($index) {
            $index->optimize();
            $index->commit();
        }

        $index  = $path = null;
        $rowset = $select->addFilterByAvailability()
            ->fetchRowset();

        foreach ($rowset as $row) {
            $_path = $indexer->getIndexPath(
                $row->site_id  . '/' . $row->locale
            );
            //next index
            if ($path !== $_path) {
                //save prev index
                if ($index) {
                    $index->optimize();
                    $index->commit();
                }
                $path = $_path;
                $index = $indexer->getIndex($path);
            }
            $index->addDocument(
                $indexer->getDocument($row)
            );
        }
        if ($index) {
            $index->optimize();
            $index->commit();
        }
    }

    public function updateSearchIndexOnCmsPageAddSuccess(array $data)
    {
        $pageId = $data['page_id'];
        $indexer = Axis::model('search/indexer');
        $rowset = Axis::model('cms/page_content')->select('*')
            ->join('cms_page', 'cp.id = cpc.cms_page_id', 'is_active')
            ->join('cms_page_category', 'cpc2.cms_page_id = cpc.cms_page_id')
            ->join('cms_category',
                'cc.id = cpc2.cms_category_id',
                'site_id'
            )->joinRight('locale_language',
                'll.id = cpc.language_id',
                'locale'
            )->where('cpc.cms_page_id = ?', $pageId)
            ->order('cc.site_id')
            ->order('cpc.language_id')
            ->fetchRowset();

        $index  = $path = null;
        foreach ($rowset as $row) {
            $_path = $indexer->getIndexPath(
                $row->site_id  . '/' . $row->locale
            );
            //next index
            if ($path !== $_path) {
                //save prev index
                if ($index) {
                    $index->optimize();
                    $index->commit();
                }
                $path = $_path;
                $index = $indexer->getIndex($path);
            }
            $hits = $index->find("url:$row->link");
            if (count($hits)) {
                $index->delete(current($hits));
            }
            if ($row->is_active) {
                $index->addDocument(
                    $indexer->getDocument($row)
                );
            }
        }
        if ($index) {
            $index->optimize();
            $index->commit();
        }
    }

    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'catalog' => array(
                'pages' => array(
                    'reports' => array(
                        'pages' => array(
                            'search/index' => array(
                                'label'         => 'Search Queries',
                                'order'         => 10,
                                'translator'    => 'Axis_Search',
                                'module'        => 'Axis_Search',
                                'route'         => 'admin/search'
                            )
                        )
                    )
                )
            )
        ));
    }
}
