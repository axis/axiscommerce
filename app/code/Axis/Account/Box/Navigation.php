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
 * @package     Axis_Account
 * @subpackage  Axis_Account_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Box_Navigation extends Axis_Account_Box_Abstract
{
    protected $_title = 'Account';
    protected $_class = 'box-account';
    protected $_items = array();

    public function addItem($shortLink, $title, $cssClass, $sortOrder = null)
    {
        if (null === $sortOrder) {
            if (empty($this->_items)) {
                $sortOrder = 0;
            } else {
                $sortOrder = max(array_keys($this->_items)) + 10;
            }
        }
        $this->_items[$sortOrder] = new Axis_Object(array(
            'href'     => $shortLink,
            'title'    => $title,
            'cssClass' => $cssClass
        ));
        
        return $this;
    }

    public function init()
    {
        if ($this->identity = Axis::getCustomerId()) {
            //@todo use event 
            $this->addItem('account', 'My Account', 'link-account')
                ->addItem('account/info/change', 'Change Info', 'link-change-info')
                ->addItem('account/address-book', 'Address Book', 'link-address-book')
                ->addItem('account/order', 'My Orders', 'link-orders')
                ->addItem('account/wishlist', 'My Wishlist', 'link-wishlist');
            
            if (Axis::single('core/module')->getByCode('Axis_Tag')->isInstalled()) {
                $this->addItem('account/tag', 'My Tags', 'link-tags');
            }
            $this->addItem('account/auth/logout', 'Logout', 'link-logout');
//
            $this->items = $this->_items;
        }
    }
}