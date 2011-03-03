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
        $this->view->sitesList = Axis::single('core/site')
            ->fetchAll()->toArray();
        $this->view->sites = Axis_Collect_Site::collect();
        $this->view->engines = array_values(
            Axis::single('sitemap/file_engine')->getEngines()
        );
        $this->render();
    }

    private function _generateXmlSitemap($config)
    {
        /*
         * Get categories
         */
        $this->view->categories = current(Axis::single('catalog/category')
            ->getFlatTree($config['lang_id'], $config['site_id'], true)
        );
        $conf = Axis::config()->sitemap;

        $changefreq['categories'] = $conf->categories->frequency ;
        $priority['categories'] = $conf->categories->priority ;

        /*
         * Get products
         */
        $this->view->products = Axis::single('catalog/product_category')
            ->getAllActiveProducts($config['lang_id'], $config['site_id']);

        $changefreq['products'] = $conf->products->frequency;
        $priority['products']   = $conf->products->priority;

        /*
         * Get cms pages
         */
        $tableCmsCategory = Axis::single('cms/category');
        $categories       = $tableCmsCategory->getActiveCategory();

        $categoryIds = array ();
        foreach ($categories as $category) {
             $categoryIds[] = $category['id'];
        }
        $pages = array();
        if ($conf->cms->showPages) {
            $pages = Axis::single('cms/page')
                ->getPageListByActiveCategory($categoryIds, $config['lang_id']);
        }
        $this->view->pages     = $pages;
        $this->view->pagesCats = $categories;
        $changefreq['pages'] = $conf->cms->frequency;
        $priority['pages']   = $conf->cms->priority;

        $this->view->serverName = Axis::single('core/site')
            ->find($config['site_id'])
            ->current()
            ->base;

        $this->view->changefreq = $changefreq;
        $this->view->priority   =  $priority;

        $script = $this->getViewScript('xml', false);
        $xml = $this->view->render($script);

        $dir = Axis::config()->system->path . '/' ;
        if (!is_dir($dir) && !mkdir($dir)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Dir %s not exist', $dir
                )
            );
            return false;
        }
        $filename = $dir . $config['filename'] . '.xml';
        if (@file_put_contents($filename, $xml)) {

            return true;
        }
        Axis::message()->addError(
            Axis::translate('core')->__(
                'Error write file : %s dir chmod 755 need', $filename
            )
        );

        return false;
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

    public function saveAction()
    {
        $this->layout->disableLayout();
        $data = $this->_getAllParams();
        if (!sizeof($data)) {
            return $this->_helper->json->sendFailure();
        }
        $alpha = new Zend_Filter_Alnum();

        if (($alpha->filter($data['filename']) != $data['filename'])
            || ($data['filename'] == '')) {

            Axis::message()->addError(
                Axis::translate('core')->__(
                    "Incorect filename %s", $data['filename']
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $filename = Axis::config()->system->path . '/'
                  . $alpha->filter($data['filename']) . '.xml';

        $rows = Axis::single('sitemap/file')->find($data['id']);

        if ($rows->count()) {
            $config = $rows->current();
            /* remove old file */
            $oldFilename = Axis::config()->system->path . '/'
                         . $config->filename . '.xml';

            if (is_file($oldFilename)) {
                unlink($oldFilename);
            }
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
        if ($rows->count()) {// if edit, then update
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
        $config = Axis::single('sitemap/file')->find($data['id'])->current()->toArray();
        $config['lang_id'] = Axis_Locale::getLanguageId();
        $this->_helper->json->sendJson(array(
            'success' => $this->_generateXmlSitemap($config)
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
            $filename = Axis::config()->system->path . '/'
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