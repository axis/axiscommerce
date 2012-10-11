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
 * @copyright   Copyright 2008-2012 Axis
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
        $this->setTitle(Axis::translate('search')->__('Search'));
        $this->render();
    }

    public function resultAction()
    {
        $this->_helper->breadcrumbs(array(
            'label' => Axis::translate('search')->__('Search'),
            'route' => 'search'
        ));

        if (!$this->_hasParam('q')) {
            return $this->_forward('not-found', 'Error', 'Axis_Core');
        }

        $paging   = array();
        $query = trim($this->_getParam('q', ''));
        $this->setTitle(
            Axis::translate('search')->__(
                "Search results for '%s'", $query
        ));
        $this->view->query = $query;
        $paging['page'] = $page = (int) $this->_getParam('page', 1);

        if (empty($query)) {
            $this->render();
            return;
        }

        try {
            $lucene = Axis::single('search/lucene')
                ->addQuery($query)
                ->addSiteFilter(Axis::getSiteId())
                ->addLocaleFilter(Axis::locale()->toString())
            ;
        } catch (Exception $e) {
            Axis::message()->addError($e->getMessage());
            $this->view->results = array();
            $this->render();
            return;
        }

        Axis::single('search/log')->logging(array(
            'num_results' => $lucene->count(),
            'query'       => $query,
        ));
        if (!$lucene->count()) { // if nothing found
            $this->view->results = array();
            $this->render();
            return;
        }

        $paging['perPage'] = array();
        $perPageArray = explode(',', Axis::config('catalog/listing/perPage'));
        foreach ($perPageArray as $perPage) {
            $url = $this->view->url(array(
                'limit' => $perPage, 'page' => null, 'q' => $query
            ));
            $paging['perPage'][$url] = $perPage;
        }

        if ($this->_hasParam('limit')
            && in_array($this->_getParam('limit'), $perPageArray)) {

            $limit = (int) $this->_getParam('limit');
        } elseif (Axis::session('catalog')->limit) {
            $limit = Axis::session('catalog')->limit;
        } else {
            $limit = Axis::config('catalog/listing/perPageDefault');
        }

        $paging['limit'] = $limit;
        $paging['page']  = $page = (int) $this->_getParam('page', 1);
        $paging['count'] = $lucene->count();//count($result);

        $this->setCanonicalUrl($this->view->url(array(
            'q'     => $query,
            'page'  => $page,
            'limit' => $limit
        )), 'search_result', true);

        // Axis::session('catalog')->limit = $limit;
        if ('all' === $limit) {
            $paging['limit'] = $paging['count'];
            $limit = $paging['count'];
        }

        $this->view->paging = $paging;

        $result = $lucene->toArray();
        //@todo ->limitPage($page, $limit)->toArray();
        $founded = array();
        for ($i = ($page - 1) * $limit, $n = $i + $limit;
             isset($result[$i])  &&  $i < $n;
             $i++)
        {
            $founded[] = $result[$i];
        }
        Axis::dispatch('search_use', array(
            'query'       => $query,
            'result'      => $founded,
            'customer_id' => Axis::getCustomerId()
        ));
        $this->view->results = $founded;
        $this->render();
    }
}
