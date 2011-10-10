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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Admin_CacheController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__(
            'Cache Management'
        );
        $this->render();
    }

    public function listAction()
    {
        $data = Axis::single('core/cache')->getList();
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess()
        ;
    }

    public function batchSaveAction()
    {
        $dataset = Zend_Json::decode($this->_getParam('data'));
        $model = Axis::model('core/cache');
        foreach ($dataset as $_data) {
            $model->save($_data);
        }
        
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $tags = Zend_Json::decode($this->_getParam('data'));

        $model = Axis_Core_Model_Cache::getCache();
        
        if (null !== $tags) {
            $success = $model->clean('matchingAnyTag', $tags);
        } else {
            $success = $model->clean();
        }
        if (!$success) {
            return $this->_helper->json->sendFailure();
        }
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Cache was cleared successfully'
            )
        );
        
        return $this->_helper->json->sendSuccess();
    }
}