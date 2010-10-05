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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_SearchController extends Axis_Admin_Controller_Back
{
    /**
     *
     * @var Axis_Search_Model_Log
     */
    protected $_table;
    
    public function init()
    {
        parent::init();
        $this->_table = Axis::single('search/log');
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('Axis_Search')->__(
            'Search Queries'
        );
        
        if ($this->_hasParam('customerId')) {
            $this->view->customerId = $this->_getParam('customerId');
        }
        if ($this->_hasParam('searchId')) {
            $this->view->searchId = $this->_getParam('searchId');
        }
        
            
        $this->view->today = $this->_table->getCount(array(
            'where' => "created_at > '" 
                    . Axis_Date::now()->getStartDay()
                        ->toPhpString('Y-m-d') . ' 00:00:00\''
        ));
        
        $this->view->lastweek = $this->_table->getCount(array(
            'where' => "created_at > '" 
                    . Axis_Date::now()->getStartWeek()
                        ->toPhpString('Y-m-d') . ' 00:00:00\''
        ));
        $this->view->lastmonth = $this->_table->getCount(array(
            'where' => "created_at > '" . Axis_Date::now()->getStartMonth()
                        ->toPhpString('Y-m-d') . ' 00:00:00\''
        ));
        $this->view->nullresult = $this->_table->getCount(array(
            'where' => 'num_results = 0'
        ));
       
        $this->render();
    }

    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        
        $field = new Axis_Filter_DbField();
        $params = array(
            'start' => (int) $this->_getParam('start', 0),
            'limit' => (int) $this->_getParam('limit', 20),
            'sort' => $field->filter($this->_getParam('sort', 'id')),
            'dir' => $field->filter($this->_getParam('dir', 'DESC')),
            'languageId' => $this->_langId,
            'getCustomerEmail' => true,
            'getQuery' => true,
            'filters' => $this->_getParam('filter', array())
        );
        $dataset = $this->_table->getList($params);
        $this->_helper->json->sendSuccess(array(
            'data' => array_values($dataset),
            'count'   => $this->_table->getCount($params),
        ));
    }
    
    public function deleteAction()
    {
        $this->getHelper('layout')->disableLayout();
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!count($ids)) {
            return;
        }
        $this->_table->delete($this->db->quoteInto('id IN(?)', $ids));
        $this->_helper->json->sendSuccess();
    }
}
