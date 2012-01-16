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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Admin_PageController extends Axis_Admin_Controller_Back
{   
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__('Pages');
        $this->render();
    }
    
    public function listAction()
    {
        $data = Axis::single('core/page')->fetchAll()
            ->toArray();
        
        return $this->_helper->json
            ->setData(array_values($data))
            ->sendSuccess()
        ;
    }
    
    public function batchSaveAction()
    {   
        $dataset = Zend_Json::decode($this->_getParam('data'));
        
        if (!sizeof($dataset)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
            ));
            return $this->_helper->json->sendFailure();
        }
        $model = Axis::model('core/page');
        
        foreach ($dataset as $data) {
            $model->save($data);
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        
        return $this->_helper->json->sendSuccess();        
    }
    
    public function removeAction()
    {
        $ids = Zend_Json::decode($this->_getParam('data'));
        
        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        Axis::single('core/page')->delete(
            $this->db->quoteInto('id IN(?)', $ids)
        );

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}
