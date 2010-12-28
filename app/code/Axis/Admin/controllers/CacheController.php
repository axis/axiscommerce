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
class Axis_Admin_CacheController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__(
            'Cache Management'
        );
        $this->render();
    }

    public function getListAction()
    {
        return $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('core/cache')->getList()
        ));
    }

    public function saveAction()
    {
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));

        return $this->_helper->json->sendJson(array(
            'success' => Axis::single('core/cache')->save($data)
        ));
    }

    public function cleanAction()
    {
        $tags = Zend_Json_Decoder::decode($this->_getParam('data'));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Cache was cleared successfully'
            )
        );
        return $this->_helper->json->sendJson(array(
            'success' => Axis_Core_Model_Cache::getCache()->clean('matchingAnyTag', $tags)
        ));
    }

    public function cleanAllAction()
    {
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Cache was cleared successfully'
            )
        );
        return $this->_helper->json->sendJson(array(
            'success' => Axis_Core_Model_Cache::getCache()->clean()
        ));
    }
}