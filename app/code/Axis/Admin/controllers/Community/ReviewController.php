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
 * @copyright   Copyright 2008-2011 Axis
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

        $sort = $this->_getParam('sort', 'id');
        $mReview = Axis::model('community/review');
        $select = $mReview->select('id')
            ->distinct()
            ->calcFoundRows()
            ->addProductDescription()
            ->addRating()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 10),
                $this->_getParam('start', 0)
            )
            ->order($sort . ' ' . $this->_getParam('dir', 'DESC'));

        $ids    = $select->fetchCol();
        $count  = $select->foundRows();
        $data   = array();
        if ($ids) {
            $data = $mReview->select('*')
                ->addProductDescription()
                ->addRating()
                ->where('cr.id IN (?)', $ids)
                ->order($sort . ' ' . $this->_getParam('dir', 'DESC'))
                ->fetchAssoc();

            $ratings = $mReview->loadRating(array_keys($data));
            foreach ($data as $key => &$review) {
                $review['ratings'] = $ratings[$key];
            }
        }

        $this->_helper->json->sendSuccess(array(
            'count' => $count,
            'data'  => array_values($data)
        ));
    }

    public function getProductListAction()
    {
        $this->_helper->layout->disableLayout();

        $mProduct = Axis::model('catalog/product');
        $select = $mProduct->select('id');

        if ($this->_hasParam('id')) {
            $select->where('cp.id = ?', $this->_getParam('id'));
        } elseif ($this->_getParam('query') != '') {
            $select->addFilter('cpd.name', $this->_getParam('query'), 'LIKE');
        }

        $list = $select->addDescription()
            ->limit(
                $this->_getParam('limit', 40),
                $this->_getParam('start', 0)
            )
            ->order(array('cpd.name ASC', 'cp.id DESC'))
            ->fetchList();

        $this->_helper->json->sendSuccess(array(
            'totalCount' => $list['count'],
            'data'       => array_values($list['data'])
        ));
    }

    public function getCustomerListAction()
    {
        $this->_helper->layout->disableLayout();

        $select = Axis::model('account/customer')->select('*')
            ->calcFoundRows()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 40),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        if ($customerId = $this->_getParam('id')) {
            $select->where('ac.id = ?', $customerId);
        } elseif ($query = $this->_getParam('query')) {
            $select->where('ac.email LIKE ?', '%' . $query . '%');
        }

        $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
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