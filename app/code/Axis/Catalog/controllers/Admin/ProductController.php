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
 * @subpackage  Axis_Catalog_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Admin_ProductController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('catalog')->__(
            'Products Catalog'
        );

        if ($this->_hasParam('productId')) {
            $this->view->productId = $this->_getParam('productId');
        }
        
        $this->view->manufacturers = Axis_Catalog_Model_Option_Product_Manufacturer::getConfigOptionsArray();
        $this->view->taxs = Axis_Tax_Model_Option_Class::getConfigOptionsArray();
        $this->render();
    }

    //@todo merge all list
    public function listAction()
    {
        if ($this->_hasParam('catId')) {
            if ($catId = $this->_getParam('catId', 0)) {
                $category = Axis::model('catalog/category')
                    ->find($this->_getParam('catId', 0))
                    ->current();
            } else {
                $category = 0; // uncategorized products filter
            }
        } elseif ($this->_hasParam('siteId')) {
            // used in order window
            $category = Axis::model('catalog/category')
                ->getRoot($this->_getParam('siteId'));
        } else {
            $category = false; // do not filter by category or site
        }

        $model = Axis::model('catalog/product');
        $select = $model->select('id')->distinct()->calcFoundRows();

        if ($category instanceof Axis_Db_Table_Row) {
            $select
                ->joinCategory()
                ->addFilter('cc.site_id', $this->_getParam('siteId', Axis::getSiteId()));
            if ($category->lvl != 0) {
                $select->addFilter('cc.id', $category->id);
            }
        } elseif (0 === $category) {
            $select->addFilterByUncategorized();
        } elseif (null === $category) {
            // user wanted to filter by non-existing category
        }

        $select->addDescription()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 10),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        if (!$ids = $select->fetchCol()) {
            return $this->_helper->json->sendSuccess(array(
                'count' => 0,
                'data'  => array()
            ));
        }

        $count = $select->foundRows();

        $products = $select->reset()
            ->from('catalog_product', '*')
            ->addCommonFields()
            ->where('cp.id IN (?)', $ids)
            ->fetchProducts($ids);

        return $this->_helper->json->sendSuccess(array(
            'data'  => array_values($products),
            'count' => $count
        ));
    }

    public function simpleListAction()
    {
        $model = Axis::model('catalog/product');
        $select = $model->select('id');

        if ($this->_hasParam('id')) {
            $select->where('cp.id = ?', $this->_getParam('id'));
        } elseif ($this->_getParam('query') != '') {
            $select->addFilter('cpd.name', $this->_getParam('query'), 'LIKE');
        }

        $list = $select->addDescription()
            ->limit(
                $this->_getParam('limit', 40),
                $this->_getParam('start', 0)
            )
            ->order(array('cpd.name ASC', 'cp.id DESC'))
            ->fetchList();

        return $this->_helper->json
            ->setData(array_values($list['data']))
            ->setTotalCount($list['count'])
            ->sendSuccess()
        ;
    }

    public function listBestsellerAction()
    {
        $select = Axis::model('catalog/product')->select('id')
            ->where('cp.ordered > 0')
            ->limit(
                $this->_getParam('limit', 10),
                $this->_getParam('start', 0)
            )
            ->order(array('cp.ordered DESC', 'cp.id DESC'));

        if ($siteId = $this->_getParam('siteId', 0)) {
            $select->joinCategory()
                ->where('cc.site_id = ?', $siteId);
        }

        $list = $select->fetchList();

        $currency = Axis::single('locale/currency')
            ->getCurrency(Axis::config()->locale->main->currency);
        foreach ($list['data'] as &$_row) {
            $_row['price'] = $currency->toCurrency($_row['price']);
        }

        return $this->_helper->json
            ->setData(array_values($list['data']))
            ->setCount($list['count'])
            ->sendSuccess()
        ;
    }

    public function listViewedAction()
    {
        $select = Axis::model('catalog/product')->select('id')
            ->where('cp.viewed > 0')
            ->limit(
                $this->_getParam('limit', 10),
                $this->_getParam('start', 0)
            )
            ->order(array('cp.viewed DESC', 'cp.id DESC'));

        if ($siteId = $this->_getParam('siteId', 0)) {
            $select->joinCategory()
                ->where('cc.site_id = ?', $siteId);
        }

        $list = $select->fetchList();

        $currency = Axis::single('locale/currency')
            ->getCurrency(Axis::config()->locale->main->currency);
        foreach ($list['data'] as &$_row) {
            $_row['price'] = $currency->toCurrency($_row['price']);
        }

        return $this->_helper->json
            ->setData(array_values($list['data']))
            ->setCount($list['count'])
            ->sendSuccess()
        ;
    }

    public function loadAction()
    {
        if ($this->_hasParam('id')) {
            $productId = (int) $this->_getParam('id');
        } else {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Invalid parameter recieved. ProductId is required'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $data = array();

        /**
         * @var Axis_Catalog_Model_Product_Row
         */
        $product = Axis::single('catalog/product')->find($productId)->current();
        if (!$product) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Product %s not found', $productId
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $data['product'] = $product->toArray();

        /* get hurl */
        $data['key_word'] = Axis::single('catalog/hurl')->getProductUrl($product->id);

        /* get description */
        $descriptions = Axis::single('catalog/product_description')
            ->select(array('language_id', '*'))
            ->where('product_id = ? ', $product->id)
            ->fetchAssoc();
        foreach (Axis_Locale_Model_Option_Language::getConfigOptionsArray() as $languageId => $values) {
            $data['description']['lang_' . $languageId] = array();
            if (!isset($descriptions[$languageId])) {
                continue;
            }
            $data['description']['lang_' . $languageId] = $descriptions[$languageId];
        }

        /* get categories with marker 'belongs_to' */
        $categories = Axis::single('catalog/category')->getNestedTreeData();
        $data['belongs_to'] = array_keys($product->getCategories());
        foreach ($categories as &$category) {
            if (in_array($category['id'], $data['belongs_to'])) {
                $category['belongs_to'] = 1;
            } else {
                $category['belongs_to'] = 0;
            }
        }
        $data['categories'] = $categories;

        /* get special  price */
        $data['special'] = Axis::single('discount/discount')
            ->getSpecialPrice($product->id);

        /* get variations */
        $data['variations'] = array();
        foreach ($product->findDependentRowset(
                'Axis_Catalog_Model_Product_Variation') as $variation) {

            $data['variations'][$variation->id] = $variation->toArray();
        }

        /* get attributes */
        $optionIds = array();
        $optionValueIds = array();
        $attributes = array();
        foreach ($product->findDependentRowset(
                'Axis_Catalog_Model_Product_Attribute', 'Product') as $attr) {

            /* collect ids for future load labels */

            if ($attr->option_value_id) {
                $optionValueIds[$attr->option_value_id] =
                    $attr->option_value_id;
            }/* else {
                continue;
            }*/
            $optionIds[$attr->option_id] = $attr->option_id;

            $attributes[$attr->id] = $attr->toArray();
            $option = $attr->findParentRow('Axis_Catalog_Model_Product_Option', 'Option');
            $attributes[$attr->id]['sort_order'] = $option->sort_order;
            $attributes[$attr->id]['input_type'] = $option->input_type;
            $attributes[$attr->id]['languagable'] = $option->languagable;
            if ($option->isInputable() && !$attr->isModifier()) {
                $values = array();
                $isLanguagable = true;
                foreach ($attr->findDependentRowset(
                        'Axis_Catalog_Model_Product_Attribute_Value') as $value) {

                    $values['value_' . intval($value->language_id)] = $value->attribute_value;
                    if (!$value->language_id) {
                        $isLanguagable = false;
                    }
                }
                $attributes[$attr->id]['value_name'] = $isLanguagable ?
                    Zend_Json::encode($values) : current($values);
            }
        }
        $languageId = Axis_Locale::getLanguageId();
        /* collect & fill labels */
        // options
        if (sizeof($optionIds)) {
            $optionText = Axis::single('catalog/product_option_text')
                ->select()
                ->where('option_id IN(?)', $optionIds)
                ->where('language_id = ?', $languageId)
                ->fetchAssoc();
        }
        // values
        if (sizeof($optionValueIds)) {
            $optionValueText = Axis::single('catalog/product_option_value_text')
                ->select()
                ->where('option_value_id IN(?)', $optionValueIds)
                ->where('language_id = ?', $languageId)
                ->fetchAssoc();
        }
        uasort($attributes, array($this, '_sortAttributes'));

        foreach ($attributes as &$refAttr) {
            $refAttr['option_name'] =
                isset($optionText[$refAttr['option_id']]['name']) ?
                    $optionText[$refAttr['option_id']]['name'] : '';
            if ($refAttr['option_value_id']) {
                $refAttr['value_name'] =
                    isset($optionValueText[$refAttr['option_value_id']]['name']) ?
                        $optionValueText[$refAttr['option_value_id']]['name'] : '';
            }
        }
        $data['modifiers'] = array();
        $data['properties'] = array();
        foreach ($attributes as $attr) {
            if ((bool)$attr['variation_id']) {
                $data['variations'][$attr['variation_id']]['attributes'][] = $attr;
            } else if ((bool)$attr['modifier']) {
                $data['modifiers'][] = $attr;
            } else {
                $data['properties'][] = $attr;
            }
        }
        $data['variations'] = array_values($data['variations']);

        // images
        $data['images'] = array();
        foreach (Axis::single('catalog/product_image')
                    ->getListBackend($product->id) as $image) {

            if (!isset($data['images'][$image['id']])) {
                $data['images'][$image['id']] = $image;
            }
            $data['images'][$image['id']]['title_' . $image['language_id']] = $image['title'];
        }

        foreach (array(
            'is_base'       => $product['image_base'],
            'is_listing'    => $product['image_listing'],
            'is_thumbnail'  => $product['image_thumbnail']) as $imageType => $imageId) {

            if (isset($data['images'][$imageId])) {
                $data['images'][$imageId][$imageType] = 1;
            }
        }
        $data['images'] = array_values($data['images']);

        // get stock
        $stock = Axis::single('catalog/product_stock')->find($productId)->current();
        if ($stock) {
            $data['stock'] = $stock->toArray();
        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess()
        ;
    }

    public function saveAction()
    {
        $_row = $this->_getParam('product');

        try {
            $model = Axis::model('catalog/product');
            $oldProductData = null;
            if ($oldProduct = $model->find($_row['id'])->current()) {
                $oldProductData = $oldProduct->toArray();
            }
            $product = $model->save($_row);
        } catch (Axis_Exception $e) {
            Axis::message()->addError($e->getMessage());
            return $this->_helper->json->sendFailure();
        }

        $categories = Zend_Json::decode($this->_getParam('category'));

        $product->setCategoryAssignments($categories['ids'])
            ->setStock($this->_getParam('stock'))
            ->setDescription($this->_getParam('description'))
            ->setSpecial($this->_getParam('special'))
            ->setUrl($this->_getParam('key_word'), $categories['site_ids']);

        $jsonParams = array('image', 'variation', 'modifier', 'property');
        foreach ($jsonParams as $param) {
            if ($this->_hasParam($param)) {
                $product->{'set' . ucfirst($param)}(Zend_Json::decode($this->_getParam($param)));
            }
        }

        Axis::dispatch('catalog_product_save_after', array(
            'old_data'  => $oldProductData,
            'product'   => $product
        ));

        Axis::message()->addSuccess(
            Axis::translate('core')->__('Data was saved successfully')
        );

        return $this->_helper->json
            ->setData(array('product_id' => $product->id))
            ->sendSuccess()
        ;
    }

    public function batchSaveAction()
    {
        $siteId = $this->_getParam('siteId', Axis::getSiteId());

        $_rowset = Zend_Json::decode($this->_getParam('data'));
        $model = Axis::model('catalog/product');

        foreach ($_rowset as $id => $_row) {
            $row = $model->find($id)->current();
            $oldProductData = $row->toArray();
            $row->setFromArray($_row);
            $row->save();
            Axis::dispatch('catalog_product_save_after', array(
                'old_data'  => $oldProductData,
                'product'   => $row
            ));
        }

        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                '%d product(s) was updated successfully', count($_rowset)
            )
        );

        return $this->_helper->json->sendSuccess();
    }

    private function _sortAttributes($a, $b)
    {
        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }
        return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
    }

    public function saveImageAction()
    {
        $this->_helper->layout->disableLayout();

        try {
            $uploader = new Axis_File_Uploader('image');
            $file = $uploader
                ->setAllowedExtensions(array('jpg','jpeg','gif','png'))
                ->setUseDispersion(true)
                ->save(Axis::config()->system->path . '/media/product');

            $data = array(
                'success' => true,
                'data' => array(
                    'path' => $file['path'],
                    'file' => $file['file']
                )
            );
        } catch (Axis_Exception $e) {
            $data = array(
                'success' => false,
                'messages' => array(
                    'error' => $e->getMessage()
                )
            );
        }

        return $this->getResponse()->appendBody(Zend_Json::encode($data));
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::single('catalog/hurl')->delete(
            $this->db->quoteInto("key_type = 'p' AND key_id IN(?)", $data)
        );
        Axis::dispatch('catalog_product_remove_success', array(
            'product_ids' => $data
        ));
        Axis::single('catalog/product')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        return $this->_helper->json->sendSuccess();
    }

    public function removeProductFromCategoryAction()
    {
        $productIds = Zend_Json::decode($this->_getParam('prodIds'));
        $categoryId = $this->_getParam('catId');

        Axis::model('catalog/product_category')->delete(array(
            $this->db->quoteInto('category_id = ? ', $categoryId),
            $this->db->quoteInto('product_id IN (?)', $productIds)
        ));

        Axis::dispatch('catalog_product_remove_from_category', array(
            'product_ids' => $productIds,
            'category_ids' => array($categoryId)
        ));

        return $this->_helper->json->sendSuccess();
    }

    public function removeProductFromSiteAction()
    {
        $productIds = Zend_Json::decode($this->_getParam('prodIds'));
        $siteId = $this->_getParam('siteId');
        $categoryIds = Axis::single('catalog/category')->getSiteCategories($siteId);

        Axis::single('catalog/product_category')->delete(array(
            $this->db->quoteInto('category_id IN(?)', $categoryIds),
            $this->db->quoteInto('product_id IN(?)', $productIds)
        ));

        Axis::dispatch('catalog_product_remove_from_category', array(
            'product_ids' => $productIds,
            'category_ids' => $categoryIds
        ));

        return $this->_helper->json->sendSuccess();
    }

    public function batchMoveAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        $destCategoryId = $this->_getParam('destination');
        $destCategory   = Axis::single('catalog/category')->find($destCategoryId)
            ->current();

        if (!$destCategory) {
            Axis::message()->addError(
                Axis::translate('Axis_Catalog')->__('Destination category not found')
            );
            $this->_helper->json->sendFailure();
        }

        $processed = array();

        $modelProductCategory = Axis::single('catalog/product_category');
        $modelCatalogHurl = Axis::single('catalog/hurl');
        foreach ($data as $product) {

            $productId = $product['product_id'];
            $keyWord = $modelCatalogHurl->getProductUrl($productId);
            foreach ($product['action'] as $categoryId => $action) {
                if ('cut' == $action) {
                    $modelProductCategory->delete(array(
                        $this->db->quoteInto('category_id = ?', $categoryId),
                        $this->db->quoteInto('product_id = ?', $productId)
                    ));
                    // @todo remove all but need only one
//                    $modelCatalogHurl->delete(
//                        $this->db->quoteInto("key_type = 'p' AND key_id = ?", $productId)
//                    );
                }
            }

            if (false === empty($processed[$productId])) {
                continue;
            }

            //remove if exist
            $modelProductCategory->delete(array(
                $this->db->quoteInto('category_id = ?', $destCategory->id),
                $this->db->quoteInto('product_id = ?', $productId)
            ));
            // add
            $modelProductCategory->insert(array(
                'category_id' => $destCategory->id,
                'product_id'  => $productId
            ));

            $modelCatalogHurl->save(array(
                'key_word' => $keyWord,
                'site_id'  => $destCategory->site_id,
                'key_type' => 'p',
                'key_id'   => $productId
            ));

            $processed[$productId] = true;
        }

        Axis::dispatch('catalog_product_move_after', array(
            'product_ids' => array_keys($processed),
            'category_id' => $categoryId
        ));

        return $this->_helper->json->sendSuccess();
    }

    public function updatePriceIndexAction()
    {
        $this->_helper->layout->disableLayout();

        $mPriceIndex    = Axis::model('catalog/product_price_index');
        $session        = Axis::session('price_index');

        if (!$this->_getParam('skip_session', false)) {
            $session->page = 1;
            $session->processed = 0;
            $mPriceIndex->delete("id > 0");
        }

        $select = Axis::model('catalog/product')
            ->select('*')
            ->calcFoundRows()
            ->limitPage($session->page, $this->_getParam('limit', 50));

        $products   = $select->fetchAssoc();
        $count      = $select->foundRows();

        $mPriceIndex->updateIndexesByProducts($products);
        $session->processed += count($products);

        $session->page++;
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                "%d of %d product(s) was processed",
                $session->processed,
                $count
            )
        );

        $completed = false;
        if ($count == $session->processed) {
            $completed = true;
            $session->unsetAll();
        }

        $this->_helper->json->sendSuccess(array(
            'completed' => $completed
        ));
    }
}
