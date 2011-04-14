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
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tag_AccountController extends Axis_Account_Controller_Abstract
{
    /**
     * Initialize
     * @see app/code/Axis/Account/Controller/Axis_Account_Controller_Abstract#init()
     */
    public function init()
    {
        parent::init();
        if (!Axis::single('core/module')->getByCode('Axis_Tag')->isInstalled()) {
            $this->_redirect('/account');
        }
        $this->addBreadcrumb(array(
            'label' => Axis::translate('account')->__('Tags'),
            'route' => 'tag_account'
        ));
    }

    /**
     * Render all account tags
     * @return void
     */
    public function indexAction()
    {
        $this->setTitle(Axis::translate('account')->__('My Tags'));
        
        $this->view->tags = Axis::single('tag/customer')->getMyWithWeight();
        $this->view->tags()->enableWeight();
        $this->render();
    }

    /**
     * Customer add new tag on product
     * @return void
     */
    public function addAction()
    {
        Axis::single('tag/customer')->save(
            $this->_getParam('tags'), $this->_getParam('productId')
        );
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }

    /**
     * customer remove self tag
     * @return void
     */
    public function removeAction()
    {
        $integer = new Zend_Filter_Int();

        $tagId = $integer->filter($this->_getParam('tagId'));

        Axis::single('tag/customer')->find($tagId)
            ->current()
            ->delete();

        $this->_redirect('/account/tag');
    }

    /**
     * Remove tag item for current user
     * @return void
     */
    public function removeItemAction()
    {
        $modelTagProduct = Axis::single('tag/product');

        $integer = new Zend_Filter_Int();
        $id = $integer->filter($this->_getParam('itemId'));
        $modelTagProduct->deleteMy($id);

        $tagId = $integer->filter($this->_getParam('tagId'));
        $weightTag = $modelTagProduct->weightTag($tagId);
        if (!$weightTag) {
            Axis::single('tag/customer')->find($tagId)
                ->current()
                ->delete();
            return $this->_redirect('account/tag');
        }
        $this->_redirect('tag/index/show-products/tagId/' . $tagId);
    }
}