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
 * @subpackage  Axis_Account_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_WishlistController extends Axis_Account_Controller_Account
{
    public function init()
    {
        parent::init();
        $this->view->crumbs()->add(
            Axis::translate('account')->__('My Wishlist'), '/account/wishlist'
        );
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__('My Wishlist');
        $this->view->meta()->setTitle($this->view->pageTitle);
        $this->view->wishlist = Axis::single('account/wishlist')
            ->findByCustomerId(Axis::getCustomerId());
        $this->render();
    }

    public function addAction()
    {
        $customerId = Axis::getCustomerId();
        $productId = $this->_getParam('id');
        $hasDuplicate = (bool) Axis::single('account/wishlist')->select('id')
            ->where('customer_id = ?', $customerId)
            ->where('product_id = ?', $productId)
            ->fetchOne();

        if (!$hasDuplicate) {
            $data = array(
                'customer_id' => $customerId,
                'product_id' => $productId
            );
            Axis::single('account/wishlist')->insert($data);
            Axis::dispatch('account_whishlist_add_product_success', $data);
            $this->_redirect('/account/wishlist');
        }
        Axis::message()->addError(
            Axis::translate('account')->__(
                'Selected product is already in your wishlist'
            )
        );
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }

    public function removeAction()
    {
        Axis::single('account/wishlist')->delete(array(
            $this->db->quoteInto('id = ?', $this->_getParam('id')),
            $this->db->quoteInto('customer_id = ?',
                (int) Axis::getCustomerId()
            )
        ));
        Axis::dispatch('account_whishlist_remove_product_success', array(
            'customer_id' => $this->_getParam('id'),
            'product_id' => Axis::getCustomerId()
        ));
        $this->_redirect('/account/wishlist');
    }

    public function updateAction()
    {
        Axis::single('account/wishlist')->updateComments(
            $this->_getParam('comment', array())
        );
        $this->_redirect('/account/wishlist');
    }
}