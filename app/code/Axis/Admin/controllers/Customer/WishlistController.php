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
class Axis_Admin_Customer_WishlistController extends Axis_Admin_Controller_Back
{
	private $_table;
	
	public function init()
	{
		parent::init();
		$this->_table = Axis::single('account/wishlist');
	}
	
	public function indexAction()
	{
		$this->view->pageTitle = Axis::translate('account')->__('Wishlist');
		if ($this->_hasParam('wishlistId')) {
	        $this->view->wishlistId = $this->_getParam('wishlistId');
	    }
		$this->render();
	}
	
    public function listAction()
    {
    	$field = new Axis_Filter_DbField();
        if ($this->_hasParam('wishlistId')) {
            $this->view->wishlistId = $this->_getParam('wishlistId');
        }
        $params = array(
            'start' => (int) $this->_getParam('start', 0),
            'limit' => (int) $this->_getParam('limit', 20),
            'sort' => $field->filter($this->_getParam('sort', 'id')),
            'dir' => $field->filter($this->_getParam('dir', 'DESC')),
            'getProductNames' => true,
            'languageId' => $this->_langId,
            'getCustomerEmail' => true,
            'filters' => $this->_getParam('filter', array())
        );
        $dataset = $this->_table->getList($params);
        $this->_helper->json->sendSuccess(array(
            'wishlist' => $dataset,
            'count' => $this->_table->getCount($params)
        ));
    }
	
}