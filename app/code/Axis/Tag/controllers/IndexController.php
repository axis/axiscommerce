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
class Axis_Tag_IndexController extends Axis_Core_Controller_Front
{

    public function init()
    {
        parent::init();
        $this->addBreadcrumb(array(
            'label' => Axis::translate('tag')->__('Tags'),
            'route' => 'tag'
        ));
    }

    public function indexAction()
    {
        $this->setTitle(Axis::translate('tag')->__('Tags'), null, false);
        $this->view->tags = Axis::single('tag/customer')->getAllWithWeight();
        $this->render();
    }

    public function showProductsAction()
    {
        $integer = new Zend_Filter_Int();
        $tagIds = array();
        $productIds = array();
        $tagItems = array();
        $tagName = '';

        if ($this->_hasParam('tag')) {
            $tagName = $this->_getParam('tag');
            foreach (Axis::single('tag/customer')->findByTag($tagName) as $tag) {
                $tagIds[] = $tag['id'];
            }
            $this->view->showRemove = false;
        } else {
            $tagId = $integer->filter($this->_getParam('tagId', 0));
            if (!$tagId) {
                $this->_redirect('/tag');
                return;
            }
            $tagIds[] = $tagId;
            $this->view->showRemove = (bool) Axis::getCustomerId();
            if (!$row = Axis::single('tag/customer')->find($tagId)->current()) {
                return $this->_forward('not-found', 'Error', 'Axis_Core');
            }
            $tagName = $row->name;
        }
        $this->setTitle(Axis::translate('tag')->__(
            "Products associated with tag '%s'", $tagName
        ));

        foreach ($tagIds as $tagId) {
            $rowset = Axis::single('tag/customer')->findProductsByTagId($tagId);
            $items = array();

            foreach ($rowset->toArray() as $item) {
                $productIds[] = $item['product_id'];
                $items[$item['product_id']] = $item;
            }
            $tagItems = $items + $tagItems;
        }
        $this->view->tagItems = $tagItems;
        if (count($productIds)) {
            $products = Axis::single('catalog/product')->select('*')
                ->addCommonFields()
                ->addFinalPrice()
                ->where('cp.id IN (?)', $productIds)
                ->fetchProducts($productIds);

            $this->view->products = $products;
        }
        $this->render();
    }
}