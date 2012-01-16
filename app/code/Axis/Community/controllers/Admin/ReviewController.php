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
 * @package     Axis_Community
 * @subpackage  Axis_Community_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Admin_ReviewController extends Axis_Admin_Controller_Back
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

    public function listAction()
    {
        $sort = $this->_getParam('sort', 'id');
        $model = Axis::model('community/review');
        $select = $model->select('id')
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
            $data = $model->select('*')
                ->addProductDescription()
                ->addRating()
                ->where('cr.id IN (?)', $ids)
                ->order($sort . ' ' . $this->_getParam('dir', 'DESC'))
                ->fetchAssoc();

            $ratings = $model->loadRating(array_keys($data));
            foreach ($data as $key => &$review) {
                $review['ratings'] = $ratings[$key];
            }
        }

        return $this->_helper->json
            ->setData(array_values($data))
            ->setCount($count)
            ->sendSuccess()
        ;
    }
    
    public function saveAction()
    {
        $data = $this->_getAllParams();
        
        $row = Axis::model('community/review')->save($data);

        Axis::message()->addSuccess(
            Axis::translate('community')->__(
                'Review was successfully saved'
        ));
        
        if (count($data['rating'])) {
            $row->setRating($data['rating']);
        }
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::single('community/review')->remove($data);

        return $this->_helper->json->sendSuccess();
    }
}