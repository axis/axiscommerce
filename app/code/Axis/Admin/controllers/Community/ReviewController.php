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
class Axis_Admin_Community_ReviewController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('community')->__(
            'Reviews'
        );
        if ($this->_hasParam('review')) {
            $this->view->review = $this->_getParam('review');
        }
        $this->render();
    }

    public function getListAction()
    {
        $this->_helper->layout->disableLayout();

        $where  = $this->_hasParam('where') ? $this->_getParam('where') : null;
        $limit  = $this->_hasParam('limit') ? $this->_getParam('limit') : 20;
        $page   = $this->_hasParam('page')  ? $this->_getParam('page')  : null;
        $dir    = $this->_hasParam('dir')   ? $this->_getParam('dir')   : 'DESC';
        $order  = $this->_hasParam('sort')  ? $this->_getParam('sort')  : 'cr.date_created';

        $reviews = Axis::single('community/review')->getList($where, $order, $dir, $limit, $page, true);

        $this->_helper->json->sendSuccess(array(
            'totalCount' => $reviews['count'],
            'data' => $reviews['reviews']
        ));
    }

    public function getProductListAction()
    {
        $this->_helper->layout->disableLayout();
        $filters = array(
            'available_only' => false,
            'site_ids'       => 0
        );
        if ($this->_hasParam('id')) {
            $filters['product_ids'] = $this->_getParam('id');
        } elseif ($this->_getParam('query') != '') {
            $filters['product_name'] = '%' . $this->_getParam('query') . '%';
        }

        $productList = Axis::single('catalog/product')->getList(
            $filters,
            array('cpd.name ASC', 'cp.id DESC'),
            $this->_hasParam('limit') ? $this->_getParam('limit') : 40,
            $this->_hasParam('start') ? $this->_getParam('start') : 0
        );

        $this->_helper->json->sendSuccess(array(
            'totalCount' => $productList['count'],
            'data'       => array_values($productList['products'])
        ));
    }

    public function getCustomerListAction()
    {
        $this->_helper->layout->disableLayout();
        $filters = array(
            'sort' => 'ac.email',
            'dir' => 'ASC',
            'start' => $this->_hasParam('start') ? $this->_getParam('start') : 0,
            'limit' => $this->_hasParam('limit') ? $this->_getParam('limit') : 40
        );
        if ($this->_hasParam('id')) {
            $filters['customer_id'] = $this->_getParam('id');
        } elseif ($this->_getParam('query') != '') {
            $filters['customer_email'] = $this->_getParam('query');
        }
        $data = Axis::single('account/customer')->getList($filters);
        $this->_helper->json->sendSuccess(array(
            'totalCount' => $data['count'],
            'data' => array_values($data['accounts'])
        ));
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_getAllParams();
        if (empty($data['customer_id'])) {
            $data['customer_id'] = new Zend_Db_Expr('NULL');
        }
        $data['ratings'] = array();
        foreach ($data['rating'] as $key => $rating) {
            $data['ratings'][$key] = $rating;
        }

        $this->_helper->json->sendJson(array(
            'success' => Axis::single('community/review')->save($data)
        ));
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();

        Axis::single('community/review')
            ->remove(Zend_Json_Decoder::decode($this->_getParam('data')));

        $this->_helper->json->sendSuccess();
    }
}