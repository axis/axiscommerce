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
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_CartController extends Axis_Core_Controller_Front_Secure
{
    public function indexAction()
    {
        $this->setTitle(Axis::translate('checkout')->__('Shopping Cart'));

        Axis::single('checkout/checkout')->clean();

        $request = $this->getRequest();
        if (null !== Axis::session()->lastUrl) {
            $lastUrl = Axis::session()->lastUrl;
            Axis::session()->lastUrl = null;
        } elseif ($request->getServer('HTTP_REFERER', false) !=
                $request->getScheme() . '://' . $request->getHttpHost() .
                $request->getRequestUri()) {

            $lastUrl = $request->getServer('HTTP_REFERER', false);
        } else {
            $lastUrl = $this->view->href('/');
        }
        $this->view->lastUrl    = $lastUrl;
        $this->view->scProducts = Axis::single('checkout/cart')->getProducts();
        $this->view->scTotal    = Axis::single('checkout/cart')->getTotalPrice();

        $this->render();
    }

    public function addAction()
    {
        $this->_helper->layout->disableLayout();
        $productId        = $this->_getParam('productId', 0);
        $quantity         = $this->_getParam('quantity', false);
        $modifierOptions  = $this->_getParam('modifier', array());
        $variationOptions = $this->_getParam('attribute', array());
        Axis::session()->lastUrl = $this->getRequest()->getServer('HTTP_REFERER');

        $result = Axis::single('checkout/cart')->add(
            $productId, $modifierOptions, $variationOptions, $quantity
        );

        if ($this->_hasParam('clean-wishlist')) {
            Axis::single('account/wishlist')->delete(array(
                $this->db->quoteInto('customer_id = ?', Axis::getCustomerId()),
                $this->db->quoteInto('product_id = ?', $productId)
            ));
        }
        if ($result) {
            $location = Axis::config('checkout/cart/redirect');
            if ('referer' == $location) {
                $location = $this->getRequest()->getServer('HTTP_REFERER');
            }
            return $this->_redirect($location);
        }
        $keyword = Axis::single('catalog/hurl')->getProductUrl($productId);
        $this->_redirect('/' . $this->view->catalogUrl . '/' . $keyword);
    }

    public function updateAction()
    {
        $this->_helper->layout->disableLayout();
        $data = $this->_getParam('quantity');
        foreach ($data as $itemId => $quantity) {
            Axis::single('checkout/cart')->updateItem($itemId, $quantity);
        }
        Axis::session()->lastUrl = $this->_getParam('last_url');
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }

    public function removeAction()
    {
        $this->_helper->layout->disableLayout();
        Axis::single('checkout/cart')->deleteItem($this->_getParam('scItemId', 0));
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }

    public function reOrderAction()
    {
        $this->_helper->layout->disableLayout();
        if ($this->_hasParam('orderId')) {
           $orderId = $this->_getParam('orderId');
        } else {
            $this->_redirect('/account/order');
            return;
        }

        $order = Axis::single('sales/order')->find($orderId);
        if (!sizeof($order) && $order instanceof Axis_Db_Table_Rowset) {
            $this->_redirect('/sales/order');
            return;
        }
        $products = $order->current()->getProducts();
        $ret = array();
        Axis::single('checkout/cart')->clear();

        foreach ($products as $product) {
            $attributes = array();
            if (!empty($product['attributes']) && count($product['attributes']))
                $attributes = $product['attributes'];
            $ret[$product['product_id']] =
            Axis::single('checkout/cart')->add(
                $product['product_id'],
                $attributes,
                array(),
                $product['quantity']
            );
        }
        // @todo Show add error for customer
        $this->_redirect('/checkout/cart');
    }
}