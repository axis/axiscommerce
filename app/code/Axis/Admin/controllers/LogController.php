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
class Axis_Admin_LogController extends Axis_Admin_Controller_Back
{
    /**
     *
     * @var Axis_Log_Model_Visitor
     */
    protected $_table;
    
    public function init()
    {
        parent::init();
        $this->_table = Axis::single('log/visitor');
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Pageviews');
        
        $this->render();
    }

    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        
        $field = new Axis_Filter_DbField();

        $sort    = $field->filter($this->_getParam('sort', 'id'));
        $dir     = $field->filter($this->_getParam('dir', 'DESC'));
        $limit   = $this->_getParam('limit', 40);
        $start   = $this->_getParam('start', 0);
        $filters = $this->_getParam('filter', false);

        $select = Axis::single('log/url_info')
            ->select(array('url', 'hit' => 'COUNT(*)'))
            ->calcFoundRows()
            ->joinLeft('log_url',
               'lu.url_id = lui.id',
               array('pdate' => 'LEFT(visit_at, 10) ')
            )
            ->group('pdate')
            ->group('lui.url')
            ->where('LEFT(visit_at, 10) IS NOT NULL')
            ->order($sort . ' ' . $dir)
            ->limit($limit, $start)
            ;

        $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->count())
            ->sendSuccess();
    }
}
