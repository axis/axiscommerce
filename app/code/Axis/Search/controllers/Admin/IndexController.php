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
 * @package     Axis_Search
 * @subpackage  Axis_Search_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Axis_Search_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Search_Admin_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('Axis_Search')->__('Search Queries');
        $this->render();
    }

    public function listAction()
    {
        $filter = $this->_getParam('filter', array());
        $limit  = $this->_getParam('limit', 20);
        $start  = $this->_getParam('start', 0);
        $sort   = $this->_getParam('sort', 'id');
        $dir    = $this->_getParam('dir', 'DESC');
        $select = Axis::model('search/log')->select('*')
            ->calcFoundRows()
            ->addCustomer()
            ->addQuery()
            ->addFilters($filter)
            ->limit($limit, $start)
            ->order($sort . ' ' . $dir)
        ;

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess()
        ;
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data', array()));
        if (!count($data)) {
            return;
        }

        Axis::model('search/log')->delete(
            $this->db->quoteInto('id IN (?)', $data)
        );

        return $this->_helper->json->sendSuccess();
    }
    
    public function updateIndexAction()
    {
        $indexer = Axis::model('search/indexer');
        $session = Axis::session('search_index');

        if ($this->_getParam('reset_session', false)) {
            
            $indexer->removeAllIndexesFromFilesystem();
            $session->page = 1;
            $session->processed = 0;
            $session->completeProduct = false;
        }
        $rowCount = $this->_getParam('limit', 50);
        
        if (!$session->completeProduct) {
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
                ->calcFoundRows()
                ->limitPage($session->page, $rowCount)
                ;
        } else {
            $select = Axis::model('cms/page_content')->select('*')
                ->join('cms_page_category', 'cpc2.cms_page_id = cpc.cms_page_id')
                ->join('cms_category', 
                    'cc.id = cpc2.cms_category_id',
                    'site_id'
                )->joinRight('locale_language', 
                    'll.id = cpc.language_id',
                    'locale'
                )
                ->order('cc.site_id')
                ->order('cpc.language_id')
                ->calcFoundRows()
                ;
        } 
        $rowset = $select->fetchRowset();
        $count  = $select->foundRows();
        $index  = $path = null;
        
        foreach ($rowset as $_row) {
            $_path = $indexer->getIndexPath(
                $_row->site_id  . '/' . $_row->locale
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
                $indexer->getDocument($_row)
            );
            
        }
        if ($index) {
            $index->optimize();
            $index->commit();
        }
        $session->processed += $rowset->count();
        $session->page++;
        
        Axis::message()->addSuccess(
            Axis::translate('search')->__(
                "%d of %d items(s) was processed",
                $session->processed,
                $count
            )
        );

        $completed = false;
        if ($count == $session->processed) {
            if ($session->completeProduct) {
                $completed = true;
                $session->unsetAll();
            } else {
                $session->page = 1;
                $session->processed = 0;
                $session->completeProduct = true;
            }
        }

        $this->_helper->json->sendSuccess(array(
            'completed' => $completed
        ));
    }
}
