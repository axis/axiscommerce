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
class Axis_Admin_Catalog_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('catalog')->__(
            'Products Catalog'
        );

        if ($this->_hasParam('productId')) {
            $this->view->productId = $this->_getParam('productId');
        }

        $this->render();
    }

    public function listProductsAction()
    {
        $sortMapping = array(
            'id'        => 'cp.id',
            'name'      => 'cpd.name',
            'sku'       => 'cp.sku',
            'quantity'  => 'cp.quantity',
            'price'     => 'cp.price',
            'is_active' => 'cp.is_active'
        );

        $dirs = array('ASC' => 'ASC', 'DESC' => 'DESC');
        $start = $this->_getParam('start', 0);
        $limit = $this->_getParam('limit', 10);
        $sort  = $this->_getParam('sort', 'id');
        $dir   = $this->_getParam('dir', 'DESC');

        if ($this->_hasParam('catId')) {
            $category = Axis::single('catalog/category')
                ->find($this->_getParam('catId', 0))
                ->current();
        } else {
            // used in order window
            $category = Axis::single('catalog/category')
                ->getRoot($this->_getParam('siteId'));
        }

        //$modelProduct  = Axis::single('catalog/product');
        $filters = array('available_only' => false);
        if ($category instanceof Axis_Db_Table_Row) {
            if ($category->lvl == 0) {
                $filters['site_ids'] = $this->_getParam('siteId');
            } else {
                $filters['category_ids'] = $category->id;
            }
        } else {
            $filters['site_ids'] = 0;
            $filters['uncategorized_only'] = true;
        }

        $data = Axis::single('catalog/product')->getList(
            $filters,
            $sortMapping[$sort] . ' ' . $dirs[$dir],
            $limit,
            $start
        );

        return $this->_helper->json->sendSuccess(array(
            'data'  => array_values($data['products']),
            'count' => $data['count']
        ));
    }

    public function listBestsellerAction()
    {
        $this->_helper->layout->disableLayout();

        $data = Axis::single('catalog/product')->getList(array(
                'site_ids'       => $this->_getParam('siteId', 0),
                'available_only' => false,
                'where'          => 'cp.ordered > 0'
            ),
            array('cp.ordered DESC', 'cp.id DESC'),
            $this->_getParam('limit', 5),
            $this->_getParam('start', 0)
        );

        $currency = Axis::single('locale/currency')
            ->getCurrency(Axis::config()->locale->main->currency);
        foreach ($data['products'] as &$item) {
            $item['price'] = $currency->toCurrency($item['price']);
        }

        return $this->_helper->json->sendSuccess(array(
            'data'  => array_values($data['products']),
            'count' => count($data['products'])
        ));
    }

    public function listViewedAction()
    {
        $this->_helper->layout->disableLayout();

        $data = Axis::single('catalog/product')->getList(array(
                'site_ids'       => $this->_getParam('siteId', 0),
                'available_only' => false,
                'where'          => 'cp.viewed > 0'
            ),
            array('cp.viewed DESC', 'cp.id DESC'),
            $this->_getParam('limit', 5),
            $this->_getParam('start', 0)
        );

        $currency = Axis::single('locale/currency')
            ->getCurrency(Axis::config()->locale->main->currency);
        foreach ($data['products'] as &$item) {
            $item['price'] = $currency->toCurrency($item['price']);
        }

        return $this->_helper->json->sendJson(array(
            'data'  => array_values($data['products']),
            'count' => count($data['products'])
        ));
    }

    public function getOptionsAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->_getParam('node', 0); // option_id
        $items = array();

        $modelProductOption = Axis::single('catalog/product_option');
        $leafOptions = array(
            Axis_Catalog_Model_Product_Option::TYPE_STRING,
            Axis_Catalog_Model_Product_Option::TYPE_TEXTAREA,
            Axis_Catalog_Model_Product_Option::TYPE_FILE
        );
        if (!$id) {
            // return options list
            $options = $modelProductOption
                ->select('*')
                ->calcFoundRows()
                ->addNameAndDescription(Axis_Locale::getLanguageId())
                ->fetchAll();

            foreach ($options as $item) {
                $items[] = array(
                   'text' => $item['name'],
                   'code' => $item['code'],
                   'option_name' => $item['name'],
                   'id' => $item['id'],
                   'option_id' => $item['id'],
                   'parent' => null,
                   'leaf' => in_array($item['input_type'], $leafOptions) ? true : false,
                   'input_type' => $item['input_type'],
                   'languagable' => $item['languagable']
                );
            }
        } else {
            /**
             * @var Axis_Catalog_Model_Product_Option_Row
             */
            $option = $modelProductOption->find($id)->current();

            $optionText = $option->findDependentRowset(
                'Axis_Catalog_Model_Product_Option_Text',
                'Option',
                $modelProductOption->select()->where('language_id = ?', $this->_langId)
            )->current();

            $values = $option->getValuesArrayByLanguage($this->_langId);

            foreach ($values as $value) {
                $items[] = array(
                    'text' => $value['name'],
                    'option_name' => $optionText ? $optionText->name : $option->code,
                    'option_code' => $option->code,
                    'value_name'  => $value['name'],
                    'id'          => $id . '_' . $value['id'], // prevent conflicting with parent ids
                    'option_id'   => $id,
                    'parent'      => $id,
                    'value_id'    => $value['id'],
                    'input_type'  => -1,
                    'leaf'        => true
                );
            }
        }

        $this->_helper->json->sendJson($items, false, false);
    }

    public function saveProductAction()
    {
        $this->_helper->layout->disableLayout();

        $prodId = (int)$this->_getParam('product_id', 0);
        $productRow = array();
        $productRow[$prodId] = $this->_getParam('product');

        try {
            $product = Axis::single('catalog/product')->save($productRow);
        } catch (Axis_Exception $e) {
            Axis::message()->addError($e->getMessage());
            return $this->_helper->json->sendFailure();
        }

        $categories = Zend_Json::decode($this->_getParam('category'));

        $product->setCategoryAssignments($categories['ids'])
            ->setStock($this->_getParam('stock'))
            ->setSpecial($this->_getParam('special'))
            ->setDescription($this->_getParam('description'))
            ->setUrl($this->_getParam('key_word'), $categories['site_ids']);

        $jsonParams = array('image', 'variation', 'modifier', 'property');
        foreach ($jsonParams as $param) {
            if ($this->_hasParam($param)) {
                $product->{'set' . ucfirst($param)}(Zend_Json::decode($this->_getParam($param)));
            }
        }

        if (!$prodId) {
            Axis::dispatch('catalog_product_add_success', array(
                'product' => $product
            ));
        } else {
            Axis::dispatch('catalog_product_update_success', array(
                'product' => $product
            ));
        }

        $this->_helper->json->sendSuccess(array(
            'data' => array('product_id' => $product->id))
        );
    }

    public function batchSaveProductAction()
    {
        $this->layout->disableLayout();

        $siteId = $this->_getParam('siteId', $this->_siteId);

        $data = Zend_Json::decode($this->_getParam('data'));
        $tableProduct = Axis::single('catalog/product');

        foreach ($data as $id => $values) {
            $product = $tableProduct->find($id)->current();
            $product->setFromArray($values);
            $product->save();
            Axis::dispatch('catalog_product_update_success', array(
                'product' => $product
            ));
        }

        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                '%d product(s) was updated successfully', count($data)
            )
        );

        $this->_helper->json->sendSuccess();
    }

    public function getProductDataAction()
    {
        $this->layout->disableLayout();
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
        $result = array();

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
        $result['product'] = $product->toArray();

        /* get hurl */
        $result['key_word'] = Axis::single('catalog/hurl')->getProductUrl($product->id);

        /* get description */
        $descriptions = Axis::single('catalog/product_description')
            ->select(array('language_id', '*'))
            ->where('product_id = ? ', $product->id)
            ->fetchAssoc();
        foreach (Axis_Collect_Language::collect() as $languageId => $values) {
            $result['description']['lang_' . $languageId] = array();
            if (!isset($descriptions[$languageId])) {
                continue;
            }
            $result['description']['lang_' . $languageId] = $descriptions[$languageId];
        }

        /* get categories with marker 'belongs_to' */
        $categories = Axis::single('catalog/category')->getNestedTreeData();
        $result['belongs_to'] = array_keys($product->getCategories());
        foreach ($categories as &$category) {
            if (in_array($category['id'], $result['belongs_to'])) {
                $category['belongs_to'] = 1;
            } else {
                $category['belongs_to'] = 0;
            }
        }
        $result['categories'] = $categories;

        /* get special  price */
        $result['special'] = Axis::single('discount/discount')
            ->getSpecialPrice($product->id);

        /* get variations */
        $result['variations'] = array();
        foreach ($product->findDependentRowset(
                'Axis_Catalog_Model_Product_Variation') as $variation) {

            $result['variations'][$variation->id] = $variation->toArray();
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

        /* collect & fill labels */
        // options
        if (sizeof($optionIds)) {
            $optionText = Axis::single('catalog/product_option_text')
                ->select()
                ->where('option_id IN(?)', $optionIds)
                ->where('language_id = ?', $this->_langId)
                ->fetchAssoc();
        }
        // values
        if (sizeof($optionValueIds)) {
            $optionValueText = Axis::single('catalog/product_option_value_text')
                ->select()
                ->where('option_value_id IN(?)', $optionValueIds)
                ->where('language_id = ?', $this->_langId)
                ->fetchAssoc();
        }
        uasort($attributes, array($this, '_sortAttributes'));

        foreach ($attributes as &$refAttr) {
            $refAttr['option_name'] =
                isset($optionText[$refAttr['option_id']]['name']) ?
                    $optionText[$refAttr['option_id']]['name'] : '';
            if ($refAttr['option_value_id']) {
                $refAttr['value_name'] =
                    $optionValueText[$refAttr['option_value_id']]['name'];
            }
        }
        $result['modifiers'] = array();
        $result['properties'] = array();
        foreach ($attributes as $attr) {
            if ((bool)$attr['variation_id']) {
                $result['variations'][$attr['variation_id']]['attributes'][] = $attr;
            } else if ((bool)$attr['modifier']) {
                $result['modifiers'][] = $attr;
            } else {
                $result['properties'][] = $attr;
            }
        }
        $result['variations'] = array_values($result['variations']);

        // images
        $result['images'] = array();
        foreach (Axis::single('catalog/product_image')
                    ->getListBackend($product->id) as $image) {

            if (!isset($result['images'][$image['id']])) {
                $result['images'][$image['id']] = $image;
            }
            $result['images'][$image['id']]['title_' . $image['language_id']] = $image['title'];
        }

        foreach (array(
            'is_base'       => $product['image_base'],
            'is_listing'    => $product['image_listing'],
            'is_thumbnail'  => $product['image_thumbnail']) as $imageType => $imageId) {

            if (isset($result['images'][$imageId])) {
                $result['images'][$imageId][$imageType] = 1;
            }
        }
        $result['images'] = array_values($result['images']);

        // get stock
        $stock = Axis::single('catalog/product_stock')->find($productId)->current();
        if ($stock) {
            $result['stock'] = $stock->toArray();
        }
        $this->_helper->json->sendSuccess(array(
            'data' => $result
        ));
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

            $result = array(
                'success' => true,
                'data' => array(
                    'path' => $file['path'],
                    'file' => $file['file']
                )
            );
        } catch (Axis_Exception $e) {
            $result = array(
                'success' => false,
                'messages' => array(
                    'error' => $e->getMessage()
                )
            );
        }

        return $this->getResponse()->appendBody(Zend_Json_Encoder::encode($result));
    }

    public function removeProductAction()
    {
        $productIds = Zend_Json_Decoder::decode($this->_getParam('data'));
        Axis::single('catalog/hurl')->delete(
            $this->db->quoteInto("key_type = 'p' AND key_id IN(?)", $productIds)
        );
        Axis::dispatch('catalog_product_remove_success', array(
            'product_ids' => $productIds
        ));
        return $this->_helper->json->sendJson(array(
            'status' => Axis::single('catalog/product')
                ->delete($this->db->quoteInto('id IN(?)', $productIds))
        ));
    }

    public function removeProductFromCategoryAction()
    {
        $productIds = Zend_Json_Decoder::decode($this->_getParam('prodIds'));
        $categoryId = $this->_getParam('catId');
        return $this->_helper->json->sendJson(array(
            'status' => Axis::single('catalog/product_category')->delete(array(
                $this->db->quoteInto('category_id = ? ', $categoryId),
                $this->db->quoteInto('product_id IN(?)', $productIds)
            ))
        ));
    }

    public function removeProductFromSiteAction()
    {
        $productIds = Zend_Json_Decoder::decode($this->_getParam('prodIds'));
        $siteId = $this->_getParam('siteId');
        $categoryIds = Axis::single('catalog/category')
            ->getSiteCategories($siteId);

        return $this->_helper->json->sendJson(array(
            'status' => Axis::single('catalog/product_category')->delete(array(
                $this->db->quoteInto('category_id IN(?)', $categoryIds),
                $this->db->quoteInto('product_id IN(?)', $productIds)
            ))
        ));

    }

    public function moveProductsAction()
    {
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        $destination = $this->_getParam('destination');

        $processed = array();
        foreach ($data as $product) {
            foreach ($product['action'] as $categoryId => $action) {
                if ($action == 'cut') {
                    Axis::single('catalog/product_category')->delete(array(
                        $this->db->quoteInto('product_id = ?', $product['product_id']),
                        $this->db->quoteInto('category_id = ?', $categoryId)
                    ));
                }
            }

            if ($product['product_id'] && empty($processed[$product['product_id']])) {
                Axis::single('catalog/product_category')->delete(array(
                    $this->db->quoteInto('category_id = ?', $destination),
                    $this->db->quoteInto('product_id = ?', $product['product_id'])
                ));
                Axis::single('catalog/product_category')->insert(array(
                    'category_id' => $destination,
                    'product_id' => $product['product_id']
                ));
            }

            $processed[$product['product_id']] = true;
        }

        $this->_helper->json->sendSuccess();
    }

    public function updateSearchIndexAction()
    {
//        $this->_helper->layout->disableLayout();

        @require_once(
            Axis::config()->system->path . '/scripts/searchIndexMaker.php'
        );

        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Search indexes updated successfully'
        ));

//        return $this->_helper->json->sendSuccess();
        $referUrl = $this->getRequest()->getServer('HTTP_REFERER', 'cache');
        $this->_redirect($referUrl);
    }
}