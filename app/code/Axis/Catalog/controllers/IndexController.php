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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */

class Axis_Catalog_IndexController extends Axis_Core_Controller_Front
{
    public function init()
    {
        parent::init();
        $this->hurl = Axis_HumanUri::getInstance();
        $this->_helper->breadcrumbs(array(
            'label' => Axis::translate('catalog')->__('Catalog'),
            'route' => 'product_catalog'
        ));
        $this->view->tags()->disableAction();
    }

    protected function _initCategory($id)
    {
        $categoryRow = Axis::single('catalog/category')->find($id)->current();

        if (!$categoryRow || $categoryRow->status != 'enabled') {
            return false;
        }

        $parentItems = $categoryRow->cache()->getParentItems();
        foreach ($parentItems as $_category) {
            if ($_category['status'] != 'enabled') {
                return false;
            }
            $_uri = $this->view->hurl(array(
                'cat' => array(
                    'value' => $_category['id'],
                    'seo'   => $_category['key_word']
                )
            ));
            $this->_helper->breadcrumbs(array(
                'label' => $_category['name'],
                'uri'   => $_uri
            ));
        }

        $categoryDescriptionRow = Axis::single('catalog/category_description')
            ->find($id, Axis_Locale::getLanguageId())
            ->current();

        if (!$categoryDescriptionRow) {
            return false;
        } else {
            $this->view->category = array_merge(
                $categoryRow->toArray(),
                $categoryDescriptionRow->toArray()
            );
            $this->view->pageTitle = $categoryDescriptionRow->name;
            $this->view->meta()
                ->setDescription($categoryDescriptionRow->meta_description)
                ->setTitle($categoryDescriptionRow->meta_title)
                ->setKeywords($categoryDescriptionRow->meta_keyword)
            ;
        }

        Zend_Registry::set('catalog/current_category', $categoryRow);

        return $this->view->category;
    }

    /**
     *  View category action (listing product)
     */
    public function viewAction()
    {
        if (count($this->hurl->getSeoParams())
            && !$this->hurl->hasParam('cat')
            && !$this->hurl->hasParam('manufacturer')
            && !$this->hurl->hasParam('product')) {

            return $this->_forward('not-found', 'Error', 'Axis_Core');
        }
        $this->setCanonicalUrl($this->view->hurl($this->hurl->getParams()));

        if ($this->hurl->hasParam('product') || $this->getRequest()->getParam('product')) {
            $this->_request->setActionName('product');
            return $this->productAction();
        }

        $this->view->pageTitle = Axis::translate('catalog')->__('Catalog');

        /**
         * @var Axis_Catalog_Model_Product_Select
         */
        $select = Axis::model('catalog/product')->select('id');
        $select->addFilterByAvailability()
            ->joinCategory()
            ->where('cc.site_id = ?', Axis::getSiteId());

        if ($this->hurl->hasParam('cat')) {
            if (!$category = $this->_initCategory($this->hurl->getParamValue('cat'))) {
                return $this->_forward('not-found', 'Error', 'Axis_Core');
            }
            $select->where('cc.id = ?', $category['id']);
        }

        if ($this->hurl->hasParam('manufacturer')) {
            $select->where(
                'cp.manufacturer_id = ?',
                $this->hurl->getParamValue('manufacturer')
            );
        }

        // $select->addPrice();
        if ($this->hurl->hasParam('price')) {
            $price  = explode(',', $this->hurl->getParam('price'));
            $from   = $price[0];
            $to     = isset($price[1]) ? $price[1] : null;
            $select->addFilterByFinalPrice($from, $to);
        }

        if ($this->hurl->hasParam('attributes')) {
            $select->addFilterByAttributes($this->hurl->getAttributeIds());
        }

        $paging = array();
        $paging['page'] = (int) $this->hurl->getParam('page', 1);
        if ($paging['page'] < 1) {
            $paging['page'] = 1;
        }

        $paging['perPage'] = array();
        foreach (explode(',', Axis::config('catalog/listing/perPage')) as $perPage) {
            $optionValue = $this->view->hurl(array('limit' => $perPage, 'page' => null));
            $paging['perPage'][$optionValue] = $perPage;
        }

        $paging['sortBy'] = array();
        foreach (explode(',', Axis::config('catalog/listing/sortBy')) as $order) {
            $optionValue = $this->view->hurl(array('order' => strtolower($order), 'page' => null));
            $optionText = Axis::translate('catalog')->__($order);
            $paging['sortBy'][$optionValue] = $optionText;
        }

        if ($this->hurl->hasParam('limit')
            && in_array($this->hurl->getParam('limit'), $paging['perPage'])) {

            $paging['limit'] = $this->hurl->getParam('limit');
        } elseif (Axis::session('catalog')->limit) {
            $paging['limit'] = Axis::session('catalog')->limit;
        } else {
            $paging['limit'] = Axis::config('catalog/listing/perPageDefault');
        }
        Axis::session('catalog')->limit = $paging['limit'];

        if ($this->hurl->hasParam('order')
            && in_array($this->hurl->getParam('order'), array('name', 'price'))) {

            $paging['order'] = $this->hurl->getParam('order');
        } elseif (Axis::session('catalog')->order) {
            $paging['order'] = Axis::session('catalog')->order;
        } else {
            $paging['order'] = 'name';
        }
        Axis::session('catalog')->order = $paging['order'];

        if ($this->hurl->hasParam('dir')
            && in_array($this->hurl->getParam('dir'), array('desc', 'asc'))) {

            $paging['dir'] = $this->hurl->getParam('dir');
        } elseif (Axis::session('catalog')->dir) {
            $paging['dir'] = Axis::session('catalog')->dir;
        } else {
            $paging['dir'] = 'asc';
        }
        Axis::session('catalog')->dir = $paging['dir'];

        switch ($paging['order']) {
            case 'name':
                $select->addDescription()
                    ->order('cpd.name' . ' ' . $paging['dir']);
                break;
            case 'price':
                $select->orderByFinalPrice($paging['dir']);
                break;
        }

        $productList = $select->limit(
                $paging['limit'] == 'all' ? 0 :  $paging['limit'],
                ($paging['page'] - 1) * ($paging['limit'] == 'all' ? 0 : $paging['limit'])
            )
            ->fetchList();

        $paging['count'] = $productList['count'];
        $this->view->products = $productList['data'];

        $paging['pages'] = 1;
        if ($paging['limit'] != 'all') {
            $paging['pages'] = ceil($paging['count'] / $paging['limit']);
        }

        $this->view->paging = $paging;
        if ($this->hurl->hasParam('mode')) {
            $mode = $this->hurl->getParam('mode');
        } elseif (Axis::session('catalog')->mode) {
            $mode = Axis::session('catalog')->mode;
        } else {
            $mode = Axis::config()->catalog->listing->type;
        }
        $this->view->mode = $mode;
        Axis::session('catalog')->mode = $mode;

        $this->render('listing');
    }

    public function productAction()
    {
        $productId = $this->hurl->getParamValue('product');
        if (!$productId) {
            $productId = $this->_getParam('product', 0);
        }
        /**
         * @var $product Axis_Catalog_Model_Product_Row
         */
        $product = Axis::single('catalog/product')
            ->cache($productId /*Axis::getCustomerId()*/)
            ->find($productId)
            ->current();

        if (!$product || !$product->is_active) {
            return $this->_forward('not-found', 'Error', 'Axis_Core');
        }

        $refCategory = false;
        if ($referer = $this->getRequest()->getServer('HTTP_REFERER')) {
            preg_match(
                '/' . $this->view->catalogUrl . '\/(.[^\/]+)/',
                $referer,
                $categoryUrl
            );
            if (isset($categoryUrl[1])) {
                $refCategory = Axis::single('catalog/category')->getByUrl($categoryUrl[1]);
            }
        }

        if ($refCategory) {
            $pathItems = $product->getParentItems($refCategory->id);
        } else {
            $pathItems = $product->getParentItems();
        }
        $lastItem = null;
        foreach ($pathItems as $_category) {
            if ($_category['status'] != 'enabled') {
                return $this->_forward('not-found', 'Error', 'Axis_Core');
            }

            $_uri = $this->view->hurl(array(
                'cat' => array(
                    'value' => $_category['id'],
                    'seo' => $_category['key_word']
                )
            ), false, true);
            $this->_helper->breadcrumbs(array(
                'label' => $_category['name'],
                'uri'   => $_uri
            ));
            $lastItem = $_category;
        }

        $product->incViewed();
        Zend_Registry::set('catalog/current_product', $product);

        if (count($pathItems)) {
            if ($refCategory && $refCategory->id == $lastItem['id']) {
                $category = $refCategory;
            } else {
                $category = Axis::single('catalog/category')
                    ->find($lastItem['id'])
                    ->current();
            }
            Zend_Registry::set('catalog/current_category', $category);
        }

        $data = $product->toArray();
        $data['category'] = $lastItem;
        $data['images'] = Axis::single('catalog/product_image')
            ->cache($productId)
            ->getList($productId);
        $data['images'] = current($data['images']);
        $data['human_url']   = urlencode($this->hurl->getParamSeo('product'));
        $data['description'] = $product->cache($productId)->getDescription();
        $data['properties']  = $product->cache($productId)->getProperties();
        $data['modifiers']   = $product->cache($productId)->getModifiers();
        $data['manufacturer'] = $product->cache($productId)->getManufacturer();
        foreach ($product->cache()->getVariationAttributesData() as $key => $value) {
            $data[$key] = $value;
        }
        $data['stock'] = array();
        $data['stock']['quantity'] = $data['quantity'];
        $data['stock']['is_saleable'] = (int)$product->cache($productId)->isSaleable();
        $stock = Axis::single('catalog/product_stock')
            ->cache($productId)
            ->find($productId)
            ->current()
            ->toArray();
        $data['stock'] = array_merge($stock, $data['stock']);

        unset($data['quantity']);

        $data['price_discount'] = $product->getPrice();

        $data['price'] = $product->getPriceRules();

        $data['discount_rules'] = Axis::single('discount/discount')
            ->cache($productId)
            ->getRulesByProduct($productId);

        $data['price']['format'] = Axis::single('locale/currency')->getFormat();

        $this->view->product = $data;

        $this->view->pageTitle = $data['description']['name'];

        $metaTitle = trim($data['description']['meta_title']) == '' ?
            $data['description']['name'] : $data['description']['meta_title'];

        $metaDescription = trim($data['description']['meta_description']) == '' ?
            strip_tags($data['description']['description']) :
                strip_tags($data['description']['meta_description']);

        $this->view->meta()
            ->setTitle($metaTitle, 'product', $productId)
            ->setDescription($metaDescription)
            ->setKeywords($data['description']['meta_keyword']);

        $_uri = $this->view->hurl(array(
            'product' => array(
                'value' => $data['id'],
                'seo'   => $product->getHumanUrl()
            )
        ), false, true);
        $this->_helper->breadcrumbs(array(
            'label' => $data['description']['name'],
            'uri'   => $_uri
        ));

        Axis::dispatch('catalog_product_view', array(
            'product' => $product
        ));

        $this->render('product');
    }

    public function __call($method, $args)
    {
        $this->_request->setActionName('view');
        $this->viewAction();
    }
}