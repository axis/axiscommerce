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
 * @package     Axis_Sitemap
 * @subpackage  Axis_Sitemap_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sitemap
 * @subpackage  Axis_Sitemap_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sitemap_Admin_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('sitemap')->__('Sitemap');
        $this->view->sitesList = Axis::single('core/site')->fetchAll()
            ->toArray();
        $this->view->crawlers = array_values(
            Axis::model('sitemap/crawler')->toArray()
        );

        $this->render();
    }

    public function listAction()
    {
        $filter = $this->_getParam('filter', array());
        $limit  = $this->_getParam('limit', 20);
        $start  = $this->_getParam('start', 0);
        $sort   = $this->_getParam('sort', 'id');
        $dir    = $this->_getParam('dir', 'DESC');
        $select = Axis::single('sitemap/sitemap')->select()
            ->calcFoundRows()
            ->addFilters($filter)
            ->limit($limit, $start)
            ->order("{$sort} {$dir}")
            ;

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $_rowset = Zend_Json::decode($this->_getParam('data'));

        $model = Axis::model('sitemap/sitemap');
        foreach ($_rowset as $_row) {
            $row = $model->getRow($_row);
            $row->modified_on = Axis_Date::now()->toSQLString();
            if (!$row->id) {
                $row->created_on = $row->modified_on;
            }
            if (empty($row->status)) {
                $row->status = (int)$row->status;
            }

            $row->save();
        }
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$data) {
            return $this->_helper->json->sendFailure();
        }
        Axis::single('sitemap/sitemap')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function pingAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        if (!$data) {
            return $this->_helper->json->sendFailure();
        }
        $crawlers = Axis::model('sitemap/crawler');
        $rowset  = Axis::model('sitemap/sitemap')->find($data);

        function html2txt($document)
        {
            $document = preg_replace(array('/\n/', '/\s+/'), ' ', $document);
            $search = array(
                '@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                '@<[\/\!]*?[^<>]*?>@si',           // Strip out HTML&XML tags
                '@<style[^>]*?>.*?</style>@siU',   // Strip style tags properly
                '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
            );
            $document = preg_replace($search, '', $document);
            return $document;
        }
        $data = array();
        $client = new Zend_Http_Client();
        foreach ($rowset as $row) {
            $status = true;
            $data[$row->id] = array();
            foreach (array_filter(explode(',', $row->crawlers)) as $_crawlerId) {
                $uri = $crawlers[$_crawlerId]['uri']
                     . $this->view->baseUrl . '/' . $row->filename;

                $client->setUri($uri);
                $client->setConfig(array(
                    'maxredirects' => 0,
                    'timeout'      => 30
                ));
                $response = $client->request();
                $code = $response->getStatus();

                if (200 !== $code) {
                    $status = false;
                }

                $data[$row->id][$_crawlerId] = array(
                    'id'      => $row->id,
                    'uri'     => $uri,
                    'code'    => $code,
                    'body'    => html2txt($response->getBody()),
                    'crawler' => $crawlers[$_crawlerId]['name']
                );
            }
            $row->status = $status;
            $row->modified_on = Axis_Date::now()->toSQLString();
            $row->save();
        }
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
}