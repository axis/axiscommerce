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
 * @subpackage  Axis_Search_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Axis_Search_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Search_IndexController extends Axis_Core_Controller_Front
{
    public function indexAction()
    {
        // render only layout blocks
    }

    public function resultAction()
    {
        $paging = array();
        $queryStr = (string) $this->_getParam('q');
        $this->view->pageTitle = Axis::translate('search')->__(
            "Search results for '%s'", trim($queryStr)
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        $this->view->query = $queryStr = trim($queryStr);
        $paging['page']  = $page  = (int) $this->_getParam('page', 1);

        if (empty($queryStr)) {
            $this->render();
            return;
        }

        /*$cacheDir = Axis::config()->system->path . '/var/cache/search/';
        if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0777, true)) {
            throw new Axis_Exception(
                Axis::translate('search')->__(
                    "Can't create folder %s. Permission denied", $cacheDir
            ));
        }
        if (!is_writable($cacheDir) && !@chmod($cacheDir, 0777)) {
            throw new Axis_Exception(
                Axis::translate('search')->__(
                    "Can't write to folder %s. Permission denied", $cacheDir
            ));
        }*/

        try {
            $lucene = Axis::single('search/lucene');
        } catch (Exception $e) {
            Axis::message()->addError($e->getMessage());
            $this->view->results = array();
            $this->render();
            return;
        }

        $result = array();
        $query = $lucene->createFuzzyQuery($queryStr);

        $result = $lucene->findFuzzy($query);

        Axis::single('search/log')->logging(array(
            'num_results' => count($result),
            'query' => $queryStr,
        ));
        if (!count($result)) { // if nothing found
            $this->view->results = array();
            $this->render();
            return;
        }

        if ($this->_hasParam('limit')) {
            $limit = $this->_getParam('limit');
        } elseif (Axis::session('catalog')->limit) {
            $limit = Axis::session('catalog')->limit;
        } else {
            $limit = Axis::config('catalog/listing/perPageDefault');
        }

        $paging['limit'] = $limit;
        $paging['page']  = $page = (int) $this->_getParam('page', 1);
        $paging['count'] = count($result);

        $paging['perPage'] = array();
        foreach (explode(',', Axis::config('catalog/listing/perPage')) as $perPage) {
            $url = $this->view->url(array(
                'limit' => $perPage, 'page' => null, 'q' => $queryStr
            ));
            $paging['perPage'][$url] = $perPage;
        }

        Axis::session('catalog')->limit = $limit;
        if ('all' === $limit) {
            $paging['limit'] = $paging['count'];
            $limit = $paging['count'];
        }

        $this->view->paging = $paging;

        $founded = array();
        for ($i = ($page - 1) * $limit, $n = $i + $limit;
             isset($result[$i])  &&  $i < $n;
             $i++)
        {
            $founded[] = $lucene->getDocumentData($result[$i], $query);
        }
        foreach ($founded as &$found) {
            $found['url'] = urlencode($found['url']);
        }
        Axis::dispatch('search_use', array(
            'query' => $queryStr,
            'result' => $founded,
            'customer_id' => Axis::getCustomerId()
        ));
        $this->view->results = $founded;
        $this->render();
    }
}