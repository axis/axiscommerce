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
 * @copyright   Copyright 2008-2011 Axis
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

        $tags = array_filter(explode(',', $this->_getParam('tags')));
        $productId = $this->_getParam('productId');

        $modelCustomer = Axis::model('tag/customer');
        $modelProduct  = Axis::model('tag/product');
        $defaultStatus = $modelCustomer->getDefaultStatus();

        $customerId    = Axis::getCustomerId();
        $siteId        = Axis::getSiteId();

        $_row = array(
            'customer_id' => $customerId,
            'site_id'     => $siteId,
            'status'      => $modelCustomer->getDefaultStatus()
        );

        foreach ($tags as $tag) {

            $row = $modelCustomer->select()
                ->where('name = ?', $tag)
                ->where('customer_id = ?', $customerId)
                ->where('site_id = ?', $siteId)
                ->fetchRow();

            if (!$row) {
                $_row['name'] = $tag;
                $row = $modelCustomer->createRow($_row);
                $row->save();

                Axis::message()->addSuccess(
                    Axis::translate('tag')->__(
                        "Tag '%s' was successfully added to product", $tag
                    )
                );
            } else {
                Axis::message()->addNotice(
                    Axis::translate('tag')->__(
                        "Your tag '%s' is already added to this product", $tag
                    )
                );
            }

            // add to product relation
            $isExist = (bool) $modelProduct->select('id')
                ->where('customer_tag_id = ?', $row->id)
                ->where('product_id = ?', $productId)
                ->fetchOne();

            if (!$isExist) {
                $modelProduct->createRow(array(
                    'customer_tag_id' => $row->id,
                    'product_id'      => $productId
                ))->save();
            }

            Axis::dispatch('tag_product_add_success', array(
                'tag'        => $tag,
                'product_id' => $productId
            ));
        }

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

        $row = Axis::single('tag/customer')->find($tagId)->current();

        if ($row->customer_id === Axis::getCustomerId() &&
            $row->site_id === Axis::getSiteId()) {

            $row->delete();
        }
        $this->_redirect('/account/tag');
    }

    /**
     * Remove tag item for current user
     * @return void
     */
    public function removeItemAction()
    {
        $model = Axis::single('tag/product');

        $integer = new Zend_Filter_Int();
        $id = $integer->filter($this->_getParam('itemId'));

        $row = $model->find($id)->current();
        if (!$row) {
            return $this->_redirect('account/tag');
        }
        $rowCustomer = $row->findParentRow('Axis_Tag_Model_Customer');

        if ($rowCustomer->customer_id != Axis::getCustomerId() ||
            $rowCustomer->site_id != Axis::getSiteId()) {

            return $this->_redirect('account/tag');
        }
        $row->delete();

        if (!$model->weightTag($rowCustomer->id)) {
            $rowCustomer->delete();
            return $this->_redirect('account/tag');
        }
        $this->_redirect('tag/index/show-products/tagId/' . $rowCustomer->id);
    }
}