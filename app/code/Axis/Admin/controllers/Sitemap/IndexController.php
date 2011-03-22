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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Sitemap_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('sitemap')->__('Sitemap');
        $this->view->sitesList = Axis::single('core/site')->fetchAll()
            ->toArray();
        $this->view->sites = Axis_Collect_Site::collect();
        $this->view->engines = array_values(
            Axis::single('sitemap/file_engine')->getEngines()
        );
        $this->render();
    }

    private function _generateXmlSitemap(Axis_Db_Table_Row $row, $languageId)
    {
        /*
         * Get categories
         */
        $config = Axis::config()->sitemap;

        /*
         * Get cms pages
         */
        
        $changefreq['pages'] = $config->cms->frequency;
        $priority['pages']   = $config->cms->priority;

        $this->view->serverName = Axis::single('core/site')
            ->find($row->site_id)
            ->current()
            ->base;

        $this->view->changefreq = $changefreq;
        $this->view->priority   =  $priority;

        $script = $this->getViewScript('xml', false);
        $xml = $this->view->render($script);
        return $xml;
//        $filename = Axis::config('system/path') . '/' . $row['filename'] . '.xml';
//        if (@file_put_contents($filename, $xml)) {
//
//            return true;
//        }
//        Axis::message()->addError(
//            Axis::translate('core')->__(
//                'Error write file : %s dir chmod 755 need', $filename
//            )
//        );
//
//        return false;
    }

    private function _pingEngine($engineUrl, $xmlUrl)
    {
        $url = $engineUrl . htmlentities($xmlUrl, ENT_QUOTES, "UTF-8");
        if (!$curl = curl_init()) {
            return false;
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        if(!$response) {
            return false;
        } else {
            $info = curl_getinfo($curl);
            curl_close($curl);
            if (empty($info['http_code']))
                return false;
            elseif ($info['http_code'] != 200)
                return false;
            if ($info['size_download'] == 0)
                return false;
            if (isset($info['download_content_length'])
                && $info['download_content_length'] > 0
                && $info['download_content_length'] != $info['size_download'])
            {
                return false;
            }
        }
        $info['html_page'] = $response;

        return $info;
    }

    public function getXmlAction()
    {
        $this->layout->disableLayout();

        $filename = $this->_getParam('filename');
        $alpha = new Zend_Filter_Alnum();
        $filename = $alpha->filter($filename);
        $filename .= '.xml';
        
        $siteId = $this->_getParam('site_id');

        $this->view->categories = Axis::single('catalog/category')->select('*')
            ->addName()
            ->addKeyWord()
            ->order('cc.lft')
            ->addSiteFilter($siteId)
            ->addDisabledFilter()
            ->fetchAll()
            ;

        $config = Axis::config()->sitemap;

        $changefreq['categories'] = $config->categories->frequency ;
        $priority['categories'] = $config->categories->priority ;

        $this->view->products = Axis::single('catalog/product_category')->select()
            ->distinct()
            ->from('catalog_product_category', array())
            ->joinLeft('catalog_product',
                'cp.id = cpc.product_id',
                array('id'))
            ->addName()
            ->addKeyWord()
            ->addActiveFilter()
            ->addDateAvailableFilter()
            ->addSiteFilter($siteId)
            ->fetchAll();

        $changefreq['products'] = $config->products->frequency;
        $priority['products']   = $config->products->priority;

        $categories = Axis::single('cms/category')->select(array('id', 'parent_id'))
            ->addCategoryContentTable()
            ->columns(array('ccc.link', 'ccc.title', 'ccc.language_id'))
            ->addActiveFilter()
            ->addSiteFilter($siteId)
            ->where('ccc.link IS NOT NULL')
            ->fetchAssoc();
        $this->view->cmsCategories = $categories;
        
        $pages = array();
        if ($config->cms->showPages && !empty($categories)) {
            $pages = Axis::single('cms/page')->select(array('id', 'name'))
                ->join(array('cpca' => 'cms_page_category'),
                    'cp.id = cpca.cms_page_id',
                    'cms_category_id')
                ->join('cms_page_content',
                    'cp.id = cpc.cms_page_id',
                    array('link', 'title', 'language_id'))
                ->where('cp.is_active = 1')
                ->where('cpca.cms_category_id IN (?)', array_keys($categories))
                ->fetchAssoc()
                ;
        }
        $this->view->pages = $pages;

        $changefreq['pages'] = $config->cms->frequency;
        $priority['pages']   = $config->cms->priority;
//
//        $this->view->serverName = Axis::single('core/site')
//            ->find($siteId)
//            ->current()
//            ->base;
//
        $this->view->changefreq = $changefreq;
        $this->view->priority   =  $priority;

        $script = $this->getViewScript('xml', false);
        $content = $this->view->render($script);

        $this->getResponse()
            ->clearAllHeaders()
            ->setHeader('Content-Description','File Transfer', true)
            ->setHeader('Content-Type','application/octet-stream', true)
            ->setHeader('Content-Disposition','attachment; filename=' . $filename, true)
            ->setHeader('Content-Transfer-Encoding','binary', true)
            ->setHeader('Expires','0', true)
            ->setHeader('Cache-Control','must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Pragma','public', true)
//            ->setHeader('Content-Length: ', filesize($content), true)
            ;
        $this->getResponse()->setBody($content);


    }

    public function saveAction()
    {
        $this->layout->disableLayout();
        return;
        $data = $this->_getAllParams();
        if (!sizeof($data)) {
            return $this->_helper->json->sendFailure();
        }
        $alpha = new Zend_Filter_Alnum();

        $filename = $alpha->filter($data['filename']);
        if (empty($data['filename']) || ($filename !== $data['filename'])) {

            Axis::message()->addError(
                Axis::translate('core')->__(
                    "Incorect filename %s", $data['filename']
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $filename = Axis::config('system/path') . '/' . $filename . '.xml';

        $row = Axis::single('sitemap/file')->find($data['id'])->current();

        if ($row) {
            $row->removeFile();
        }
        /* check for file exists */
        if (is_file($filename)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'File already exist'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        if ($row->count()) {// if edit, then update
            Axis::single('sitemap/file')->update(array(
                'filename' =>  $alpha->filter($data['filename']),
                'generated_at' => Axis_Date::now()->toSQLString(),
                //'usage_at' => Axis_Date::now()->toSQLString(),
                'site_id' => $data['site_ids']
            ), $this->db->quoteInto('id = ?', $data['id']));
        } else { // else insert new
            $data['id'] = Axis::single('sitemap/file')->insert(array(
                'filename'     => $alpha->filter($data['filename']),
                'generated_at' => Axis_Date::now()->toSQLString(),
                'usage_at'     => '0000-00-00',//Axis_Date::now()->toSQLString(),
                'site_id'      => $data['site_ids'],
                'status'       => 0
            ));
        }
        //seaech engines saving
        if (!isset($data['engine_ids']))
            $data['engine_ids'] = array(1, 2, 3, 4);

        if (isset($data['engine_ids'])) {
            Axis::single('sitemap/file_engine')
                ->save($data['engine_ids'], $data['id']);
        }
        // generate xml sitemap
        $row = Axis::single('sitemap/file')->find($data['id'])->current();//->toArray();
//        $row['lang_id'] = Axis_Locale::getLanguageId();
        
        $this->_helper->json->sendJson(array(
            'success' => $this->_generateXmlSitemap($row, Axis_Locale::getLanguageId())
        ));
    }

    public function listAction()
    {
        $this->layout->disableLayout();
        if ($this->_hasParam('siteMapId')) {
            $this->view->siteMapId = $this->_getParam('siteMapId');
        }

        $filter = $this->_getParam('filter', array());
        $limit  = $this->_getParam('limit', 20);
        $start  = $this->_getParam('start', 0);
        $sort   = $this->_getParam('sort', 'id');
        $dir    = $this->_getParam('dir', 'DESC');
        $select = Axis::single('sitemap/file')->select()
            ->calcFoundRows()
            ->addFilters($filter)
            ->limit($limit, $start)
            ->order("{$sort} {$dir}")
            ;

        $data = array();
        $engineNames = Axis::single('sitemap/file_engine')
            ->getEnginesNamesAssigns();

        foreach ($select->fetchAll() as $item) {
            $item['engines'] = isset($engineNames[$item['id']]) ?
                implode(',', array_keys($engineNames[$item['id']])) : '';
            $data[] = $item;
        }
        $this->_helper->json->setSitemap($data)
            ->setCount($select->foundRows())
             ->sendSuccess();
    }

    public function removeAction()
    {
        $this->_helper->layout->disableLayout();
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$ids) {
            return $this->_helper->json->sendFailure();
        }
        foreach ($ids as $id) {
            $row = Axis::single('sitemap/file')->find($id)->current();
            $filename = Axis::config('system/path') . '/' 
                      . $row->filename . '.xml';
            if (file_exists($filename))
                @unlink($filename);
        }

        Axis::single('sitemap/file')->delete(
            $this->db->quoteInto('id IN(?)', $ids)
        );
        return $this->_helper->json->sendSuccess();
    }

    public function pingAction()
    {
        $this->_helper->layout->disableLayout();
        $ids = Zend_Json_Decoder::decode($this->_getParam('ids'));

        if (!$ids) {
            return $this->_helper->json->sendFailure();
        }
        $modelSite = Axis::single('core/site');

        $engineNames = Axis::single('sitemap/file_engine')
            ->getEnginesNamesAssigns();
        $info = '';
        foreach ($ids as $id) {
            $status = 0;
            $config = Axis::single('sitemap/file')
                ->find($id)
                ->current()
                ->toArray();

            $url = $modelSite->find($config['site_id'] )->current()->base
                 . $config['filename'] . '.xml';

            $engines = Axis::single('sitemap/file_engine')->getEngines();
            foreach ($engines as  $engine) {
                if (isset($engine['url'])
                    && isset($engineNames[$id][$engine['id']])) {

                    $info[$id][$engine['name']] =
                        $this->_pingEngine($engine['url'], $url);

                    if (false !== $info[$id][$engine['name']]) {
                        $status++;
                    }
                }
            }
            // change status
            if (count($engineNames[$id]) == $status) {
                Axis::single('sitemap/file')->update(
                    array('status' => 0, 'usage_at' => '0000-00-00'),
                    $this->db->quoteInto('site_id = ?', $config['site_id'])
                );
                Axis::single('sitemap/file')->update(array('status' =>  1,
                    'usage_at' => Axis_Date::now()->toSQLString()),
                    $this->db->quoteInto('id = ?', $id)
                );
            }
        }
        $this->_helper->json->sendSuccess(array(
            'info' => $info
        ));
    }

    public function quickSaveAction()
    {
        $this->_helper->layout->disableLayout();
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$data)  {
            return;
        }
        $tableSitemapToEngine = Axis::single('sitemap/file_engine');
        foreach ($data as $id => $item) {
            Axis::single('sitemap/file')->update(array(
                    'generated_at' => Axis_Date::now()->toSQLString(),
                    'site_id' => $item['site'],
                    'status' => 0
                ),
                $this->db->quoteInto('id = ?', $id)
            );
            $config = Axis::single('sitemap/file')->find($id)->current()->toArray();
            $config['lang_id'] = Axis_Locale::getLanguageId();
            $this->_generateXmlSitemap($config);
            $tableSitemapToEngine->save(explode(',', $item['engines']), $id);
        }
        $this->_helper->json->sendSuccess();
    }
}