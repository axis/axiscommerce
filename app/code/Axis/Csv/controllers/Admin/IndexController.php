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
 * @package     Axis_Csv
 * @subpackage  Axis_Csv_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Csv
 * @subpackage  Axis_Csv_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Csv_Admin_IndexController extends Axis_Admin_Controller_Back
{
    private $_valuesCache = array();
    private $_optionsCache = array();
    private $_supportedTypes = array(
        array('Products', 'products')/*,
        array('Customers', 'customers')*/
    );

    public function indexAction()
    {

        $this->view->pageTitle = Axis::translate('admin')->__(
            "Csv Import/Export"
        );
        $this->render();
    }

    public function listAction()
    {
        $data = Axis::single('csv/profile')->getList();
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function listTypeAction()
    {
        return $this->_helper->json->sendRaw($this->_supportedTypes);
    }

    public function runAction()
    {
        $data = $this->_getParam('general');
        $filters = $this->_getParam('filter');

        if (!method_exists($this, '_' . $data['direction'] . ucfirst($data['type']))) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Requested method not found'
                )
            );
            $this->_helper->json->sendFailure();
        }
        $messages = call_user_func(
            array($this, '_' . $data['direction'] . ucfirst($data['type'])),
            $data, $filters
        );
        return $this->_helper->json
            ->setMessages($messages)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $data         = $this->_getParam('general');
        $filters      = $this->_getParam('filter');
        $data['site'] = trim($filters['site'], ',');

        $row = Axis::model('csv/profile')->save($data);//, $filters);


        if ('export' == $row->direction) {
            $rowFilter = $row->findDependentRowset('Axis_Csv_Model_Profile_Filter')->current();
            if (!$rowFilter) {
                $rowFilter = Axis::single('csv/profile_filter')->createRow();
                $rowFilter->profile_id = $row->id;
            }
            $rowFilter->setFromArray($filters);
            $rowFilter->language_ids = trim($rowFilter->language_ids, ', ');

            $rowFilter->price_from = is_numeric($rowFilter->price_from) ?
                    $rowFilter->price_from : new Zend_Db_Expr('NULL');

            $rowFilter->price_to = is_numeric($rowFilter->price_to) ?
                    $rowFilter->price_to : new Zend_Db_Expr('NULL');

            $rowFilter->qty_from = is_numeric($rowFilter->qty_from) ?
                    $rowFilter->qty_from : new Zend_Db_Expr('NULL');

            $rowFilter->qty_to = is_numeric($rowFilter->qty_to) ?
                    $rowFilter->qty_to : new Zend_Db_Expr('NULL');
            $rowFilter->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Profile was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        Axis::single('csv/profile')->deleteByIds($data);

        return $this->_helper->json->sendSuccess();
    }

    private function _getCsvProductTitles($type = null)
    {
        $productTitles = array(
            'sku', 'manufacturer', 'hurl', 'path', 'quantity', 'image_base', 'image_listing', 'image_thumbnail',
            'price', 'weight', 'date_available', 'in_stock', 'is_active', 'ordered',
            'created_on', 'modified_on', 'tax_class');
        $descriptionTitles = array(
            'name', 'description', 'short_description', 'viewed', 'image_seo_name',
            'meta_title', 'meta_description', 'meta_keyword');

        switch ($type) {
            case 'product':
                return $productTitles; break;
            case 'description':
                return $descriptionTitles; break;
            default:
                return array_merge(
                    $productTitles, array('product_gallery', 'product_featured'),
                    $descriptionTitles, array('variations'));
                break;
        }
    }

    private function _getModifierValue($value, $type)
    {
        switch ($type) {
            case 'by':
                return ($value >= 0 ? '+' : '-') . $value;
            case 'percent':
                return $value . '%';
            case 'to':
                return $value;
            default:
                return '';
        }
    }

    private function _valueToModifier($value)
    {
        $type = 'to';
        $value = trim($value);
        if (false !== strpos($value, '%')) {
            $type = 'percent';
        } elseif (substr($value, 0, 1) == '+' || substr($value, 0, 1) == '-') {
            $type = 'by';
        }
        $value = floatval(trim($value, '%+'));
        return array($value, $type);
    }

    /**
     *
     * @return value text, this function use cache array for fast execution
     * @param int $valueId
     * @param enteger $languageId
     */
    private function _getValueText($valueId, $languageId)
    {
        if (!isset($this->_valuesCache[$valueId])) {
            $text = Axis::single('catalog/product_option_value_text')->find(
                $valueId,
                $languageId
            );

            $this->_valuesCache[$valueId] = $text->valid() ?
                $text->current()->name : '';
        }
        return $this->_valuesCache[$valueId];
    }

    private function _exportProducts($preferences, $filters)
    {
        $path = Axis::config()->system->path . '/' . $preferences['file_path'];

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (@!$fp = fopen($path . '/' . $preferences['file_name'], 'w')) {
            return array(
                'error' => Axis::translate('core')->__(
                    'Cannot open file with write permissions'
                )
            );
        }

        @chmod($path . '/' . $preferences['file_name'], 0777);

        $titles = $this->_getCsvProductTitles();
        $emptyRow = array_fill_keys($titles, '');

        // Fill descriptions with empty arrays
        $from = array_search('name', $titles);
        $to = array_search('meta_keywords', $titles);
        $data = array_fill_keys(
            array_slice($titles, $from, $to - $from + 1), array()
        );
        $emptyRow = array_merge($emptyRow, $data);

        // Searching product options
        $attrModel = Axis::single('catalog/product_attribute');
        $optionTypes = Axis_Catalog_Model_Product_Option::getTypes();
        $options = array();
        $values = array();
        foreach ($attrModel->getUsedOptions() as $option) {
            $options[$option->id] = $option;

            $title = $option->code . ',' . $optionTypes[$option->input_type];
            if ($valueset = $option->getValueset()) {
                $title .= ',' . $valueset->name;
            }
            $titles[] = $title;

            $emptyRow[$option->code] = '';
        }

        // Write titles
        fputcsv($fp, $titles, ',', "'");
        unset($titles);

        // Load languages
        $filter_languages = explode(',', trim($filters['language_ids'], ' ,'));
        $languageIds = array_keys(Axis::model('locale/option_language')->toArray());
        $languageId = Axis_Locale::getLanguageId();
        $langIdToCode = array();
        foreach (Axis::single('locale/language')->fetchAll() as $lang) {
            $langIdToCode[$lang->id] = $lang->locale;
        }

        $manufacturersSource = Axis::single('catalog/product_manufacturer')->getList();

        $manufacturers = array();
        foreach ($manufacturersSource as $manufacturer) {
            $manufacturers[$manufacturer['id']] = $manufacturer['url'];
        }

        $imageSource = Axis::single('catalog/product_image')
            ->select(array('id', 'path'))
            ->fetchAll();

        $images = array();
        $imageFields = array('image_base', 'image_listing', 'image_thumbnail');
        foreach ($imageSource as $image) {
            $images[$image['id']] = $image['path'];
        }

        // Init start values
        $end = false;
        $lastExportedId = 0;
        $step = 30;

        $mProduct = Axis::single('catalog/product');
        $mCsvProfile = Axis::single('csv/profile');
        // Export products
        while (!$end) {
            $products = $mCsvProfile->getProductSet($lastExportedId, $step, $filters);

            if (count($products) < $step) {
                $end = true;
            }
            foreach ($products as $product) {

                $lastExportedId = $product->id;

                $data = array_intersect_key($product->toArray(), $emptyRow);

                // replace image ids with paths
                foreach ($imageFields as $imageField) {
                    if (empty($data[$imageField])) {
                        continue;
                    }
                    $data[$imageField] = $images[$data[$imageField]];
                }

                // fill manufacture name
                if (!empty($product->manufacturer_id) &&
                    isset($manufacturers[$product->manufacturer_id]))
                {
                    $data['manufacturer'] = $manufacturers[$product->manufacturer_id];
                }

                // put data to row
                $row = array_merge($emptyRow, $data);

                // product paths
                $path = '';

                $paths = $product->getParentItems(null, true);
                foreach ($paths as $pathItem) {
                    foreach ($pathItem as $category)
                        $path .= '/'.$category['key_word'];
                    $path .= ",\n";
                }
                $row['path'] = substr($path, 0, -2);

                // human url
                $row['hurl'] = Axis::single('catalog/hurl')->getProductUrl($product->id);

                // stock status
                if ($stock = Axis::single('catalog/product_stock')
                        ->find($lastExportedId)->current())
                {
                    $row['in_stock'] = $stock->in_stock;
                }

                // Gallery
                $gallery = '';
                foreach ($product->getImages() as $item) {
                    $gallery .= $item->path . ",\n";
                }
                $row['product_gallery'] = substr($gallery, 0, -2);

                // Attributes
                $attributes = array();
                foreach ($product->getAttributes() as $attribute) {
                    // get option from cache
                    $option = $options[$attribute->option_id];
                    // get value from cache
                    if ($attribute->option_value_id) {
                        $value = $this->_getValueText($attribute->option_value_id, $languageId);
                    } else {
                        $value = '';
                    }

                    if ($attribute->isProperty()) {
                        // inputable attribute
                        if ($option->isInputable()) {
                            $attrValues = $attribute->getAttributeValues();
                            if (!$attrValues->valid()) {
                                continue;
                            }
                            if (!$option->languagable) {
                                $row[$option->code] = $attrValues->current()->attribute_value;
                            } else {
                                foreach ($attrValues as $attrValue) {
                                    $aLangId = $attrValue->language_id;
                                    if (!in_array($aLangId, $languageIds))
                                        continue;
                                    $row[$option->code][$aLangId] = $attrValue->attribute_value;
                                }
                            }
                        } else { // attribute with value from valueset
                            $row[$option->code] = $value;
                        }
                    } elseif ($attribute->isModifier()) {
                        // Modifier
                        if (!is_array($row[$option->code]))
                            $row[$option->code] = array();
                        $row[$option->code][] = array(
                            'value'  => $value,
                            'price'  => $this->_getModifierValue($attribute->price, $attribute->price_type),
                            'weight' => $this->_getModifierValue($attribute->weight, $attribute->weight_type)
                        );
                    } elseif ($attribute->isVariation()) {
                        // Variation
                        if (!is_array($row['variations'])) {
                            $row['variations'] = array();
                        }
                        if (!isset($row['variations'][$attribute->variation_id])) {
                            $variation = $attribute->getVariation();
                            $row['variations'][$attribute->variation_id] = array(
                                'sku'    => $variation->sku,
                                'price'  => $this->_getModifierValue($variation->price, $variation->price_type),
                                'weight' => $this->_getModifierValue($variation->weight, $variation->weight_type),
                                'quantity' => $variation->quantity
                            );
                        }
                        $row['variations'][$attribute->variation_id][$option->code] = $value;
                    }
                }
                if (is_array($row['variations']))
                    $row['variations'] = array_values($row['variations']);

                // Description
                $descriptions = array();
                $descriptionRows = $product->findDependentRowset(
                    'Axis_Catalog_Model_Product_Description',
                    'Product',
                    $mProduct->select()->where('language_id IN(?)', $languageIds)
                );

                foreach ($descriptionRows as $description) {
                    if (!in_array($description->language_id, $filter_languages)) {
                        continue;
                    }

                    $langCode = $langIdToCode[$description->language_id];
                    foreach ($description->toArray() as $key => $dValue) {
                        if (isset($row[$key])) {
                            if (!is_array($row[$key])) {
                                $row[$key] = array();
                            }
                            $row[$key][] = array($langCode => $dValue);
                        }
                    }
                }

                // Encode attributes
                foreach ($row as &$item) {
                    if (is_array($item)) {
                        $item = str_replace('"},{"', "\"},\n {\"", Zend_Json_Encoder::encode($item));
                    }
                }

                fputcsv($fp, $row, ',', "'");
            }
        }
        return array(
            'success' => 'Products was exported successfully'
        );
    }

    private function _removeDir($path) {
        if (is_dir($path)) {
            $path = rtrim($path, '/');
            $dir = dir($path);
            while (false !== ($file = $dir->read())) {
                if ($file != '.' && $file != '..') {
                    (!is_link("$path/$file") && is_dir("$path/$file")) ?
                        RemoveDir("$path/$file") : unlink("$path/$file");
                }
            }
            $dir->close();
            rmdir($path);
            return true;
        }
        return false;
    }

    private function _getCreateValue($valueText, $valuesetId, $createOption = false)
    {
        $key = $valuesetId . '_' . $valueText;
        if (!isset($this->_valuesCache[$key])) {
            $value = Axis::single('catalog/product_option_value')->getByText($valueText, $valuesetId);
            if (!$value && $createOption) {
                // creating value
                $value = Axis::single(('catalog/product_option_value'))->createRow(array(
                    'valueset_id' => $valuesetId
                ));
                $value->save();

                // caching language_ids
                if (!Zend_Registry::isRegistered('language_ids')) {
                    Zend_Registry::set('language_ids', array_keys(Axis::model('locale/option_language')->toArray()));
                }
                // insert the same text for all languages
                foreach (Zend_Registry::get('language_ids') as $langId) {
                    Axis::single('catalog/product_option_value_text')->insert(array(
                        'option_value_id' => $value->id,
                        'language_id' => $langId,
                        'name' => $valueText
                    ));
                }
            }
            $this->_valuesCache[$key] = $value;
        }
        return $this->_valuesCache[$key];
    }

    /**
     * Return option object(Row) for the gived optionName
     * It can create option if it does not exist.
     *
     * @return Axis_Catalog_Model_Product_Option_Row
     */
    private function _getCreateOption($optionName, $createNotExists = false)
    {
        // optionName: "option_name,input_type<,valueset_name>"
        $optionName = explode(',', $optionName);

        if (!isset($this->_optionsCache[$optionName[0]])) {
            $mOption = Axis::single('catalog/product_option');
            $option = $mOption->select()
                ->where('code = ?', $optionName[0])
                ->fetchRow();
            if (!$option && $createNotExists && isset($optionName[1])) {
                // create option
                $option = $mOption->createRow();

                $types = Axis_Catalog_Model_Product_Option::getTypes();
                $option->input_type = array_search($optionName[1], $types);
                $option->code = $optionName[0];

                if (!$option->isInputable() && isset($optionName[2])) {
                    $valueset  = Axis::single('catalog/product_option_valueSet')
                        ->getCreate($optionName[2]);
                    $option->valueset_id = $valueset->id;
                }
                $option->save();

                // caching language_ids
                if (!Zend_Registry::isRegistered('language_ids')) {
                    Zend_Registry::set('language_ids', array_keys(Axis::model('locale/option_language')->toArray()));
                }
                // insert the same text for all languages
                foreach (Zend_Registry::get('language_ids') as $langId) {
                    Axis::single('catalog/product_option_text')->insert(array(
                        'option_id' => $option->id,
                        'language_id' => $langId,
                        'name' => $option->code,
                        'description' => ''
                    ));
                }
            }
            $this->_optionsCache[$optionName[0]] = $option;
        }
        return $this->_optionsCache[$optionName[0]];
    }

    private function _optionNameToType($optionName)
    {
        list(,$type) = explode(',', $optionName);
        return $type;
    }

    private function _arraySearchByKeyValue(&$array, $key, $value)
    {
        foreach ($array as $index => $item) {
            if (isset($item[$key]) && $item[$key] == $value)
                return $index;
        }
        return false;
    }

    private function _importProducts($preferences, $filters)
    {
        $this->_helper->layout->disableLayout();
        $createOptions = true;

        $path = Axis::config()->system->path . '/' . $preferences['file_path'];

        if (@!$fp = fopen($path.'/'.$preferences['file_name'], 'r')) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Cannot open file with read permissions'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $titles = fgetcsv($fp, 2048, ',', "'");
        $rowSize = count($titles);
        $optionsIndexStartFrom = array_search('variations', $titles) + 1;
        $optionTypes = Axis_Catalog_Model_Product_Option::getTypes();

        $filter_sites = explode(',', trim($filters['site'], ', '));

        $mManufacturer = Axis::single('catalog/product_manufacturer');
        $mManufacturerTitle = Axis::single('catalog/product_manufacturer_description');
        $manufacturers = $mManufacturer
            ->select(array('name', 'id'))
            ->fetchPairs();

        $languages = Axis::single('locale/language')
            ->select(array('locale', 'id'))
            ->fetchPairs();

        $mProduct = Axis::single('catalog/product');
        $log['skipped'] = array();
        $log['imported'] = array();
        $log['skipped']['count'] = 0;
        $log['imported']['count'] = 0;

        while (!feof($fp)) {
            $data = fgetcsv($fp, 2048, ',', "'");

            if (!is_array($data)) {
                continue;
            }
            $data = array_pad($data, $rowSize, '');
            $data = array_combine($titles, $data);

            if (empty($data['sku'])) {
                continue;
            }

            $log['imported']['count']++;

            if (!empty($data['manufacturer'])
                && !isset($manufacturers[$data['manufacturer']])) {

                if (!$manufacturerId = $mManufacturer->select('id')
                        ->where('cpm.name = ?', $data['manufacturer'])
                        ->fetchOne()) {

                    $manufacturerId = $mManufacturer->insert(array(
                        'name' => $data['manufacturer']
                    ));
                    foreach ($languages as $langId) {
                        $mManufacturerTitle->insert(array(
                            'manufacturer_id' => $manufacturerId,
                            'language_id' => $langId,
                            'title' => $data['manufacturer']
                        ));
                    }
                    $url = $data['manufacturer'] = preg_replace('/[^a-zA-Z0-9]/', '-', $data['manufacturer']);
                    foreach ($filter_sites as $siteId) {
                        $i = 0;
                        while (Axis::single('catalog/hurl')->hasDuplicate($url, $siteId)) {
                            $url = $data['manufacturer'] . '-' . ++$i;
                        }
                        Axis::single('catalog/hurl')->insert(array(
                            'key_word'  => $url,
                            'site_id'   => $siteId,
                            'key_type'  => 'm',
                            'key_id'    => $manufacturerId
                        ));
                    }
                }
                $manufacturers[$data['manufacturer']] = $manufacturerId;
            }

            $product = $mProduct->select()
                ->where('sku = ?', $data['sku'])
                ->fetchRow();

            if (!$product) {
                $product = $mProduct->createRow();
                $product->viewed = 0;
                $product->tax_class_id = 1;
            }

            if (!empty($data['manufacturer'])
                && isset($manufacturers[$data['manufacturer']])) {

                $product->manufacturer_id = $manufacturers[$data['manufacturer']];
            }

            $product->setFromArray($data);

            // will set correct values for the images later
            foreach (array('image_base', 'image_listing', 'image_thumbnail') as $imageType) {
                if (!isset($data[$imageType])) { // data[imageType] is a string (path to image) not id
                    continue;
                }
                $product->{$imageType} = null;
            }

            $viewed = Zend_Json_Decoder::decode($data['viewed']);
            if (is_array($viewed)) {
                foreach ($viewed as $viewedPerLanguage) {
                    $product->viewed += current($viewedPerLanguage);
                }
            } else {
                $product->viewed += (int)$viewed;
            }

            $productId = $product->save();

            // human url
            $urlModel = Axis::single('catalog/hurl');
            $data['hurl'] = str_replace(array('\\', '/', ' '), '-', $data['hurl']);
            foreach ($filter_sites as $siteId) {
                $urlModel->delete("key_type = 'p' AND key_id = {$product->id}");
                $i   = 0;
                $url = $data['hurl'];
                while ($urlModel->hasDuplicate($url, $siteId)) {
                    $url = $data['hurl'] . '-' . ++$i;
                }
                $urlModel->save(array(
                    'key_word'  => $url,
                    'site_id'   => $siteId,
                    'key_type'  => 'p',
                    'key_id'    => $product->id
                ));
            }

            // create categories if not exist
            if (isset($data['path'])) {
                $paths = explode(',', trim($data['path'], ', '));
                $modelCategory = Axis::single('catalog/category');
                $mProductCategory = Axis::single('catalog/product_category');
                $mProductCategory->delete('product_id = ' . $product->id);

                $catData = array(
                    'status'      => 'enabled',
                    'modified_on' => Axis_Date::now()->toSQLString(),
                    'created_on'  => Axis_Date::now()->toSQLString()
                );

                foreach ($paths as $path) {
                    $cleanPath = trim($path, "/\n ");
                    $path = explode('/', $cleanPath);
                    foreach ($filter_sites as $siteId) {
                        $i = 0;
                        $categoryId = 0;

                        $rootCategory = $modelCategory->getRoot($siteId);

                        foreach ($path as $catUrl) {
                            if (!$category = $modelCategory->getByUrl($catUrl, $siteId)) {
                                if ($i == 0) {
                                    $categoryId = $modelCategory->insertItem(
                                        $catData,
                                        $rootCategory->id
                                    );
                                } else {
                                    $categoryId = $modelCategory->insertItem(
                                        $catData,
                                        $modelCategory
                                            ->getByUrl($path[$i-1], $siteId)
                                            ->id
                                    );
                                }
                                // description
                                foreach ($languages as $langId) {
                                    Axis::single('catalog/category_description')->save(array(
                                        'category_id'       => $categoryId,
                                        'language_id'       => $langId,
                                        'name'              => $catUrl,
                                        'description'       => '',
                                        'meta_title'        => $catUrl,
                                        'meta_description'  => '',
                                        'meta_keyword'      => $catUrl
                                    ));
                                }

                                // human url
                                Axis::single('catalog/hurl')->save(array(
                                    'key_word'  => $catUrl,
                                    'site_id'   => $siteId,
                                    'key_type'  => 'c',
                                    'key_id'    => $categoryId
                                ));
                            } else {
                                $categoryId = $category->id;
                            }
                            $i++;
                        }

                        // product to category linking
                        $assignment = $mProductCategory->select()
                            ->where('category_id = ?', $categoryId)
                            ->where('product_id = ?', $product->id)
                            ->fetchRow();

                        if (!$assignment) {
                            $mProductCategory->insert(array(
                                'category_id' => $categoryId,
                                'product_id'  => $productId
                            ));
                        }
                    }
                }
            }

            // Gallery
            if (isset($data['product_gallery'])) {
                $imagePathToId = array();
                $images = explode(',', $data['product_gallery']);
                $modelImage = Axis::single('catalog/product_image');
                $modelImage->delete('product_id = ' . $product->id);
                foreach ($images as $image) {
                    $image = trim($image); // remove line-breaks
                    $imageId = $modelImage->insert(array(
                        'product_id' => $product->id,
                        'path'       => $image
                    ));
                    $imagePathToId[$image] = $imageId;
                }

                if (isset($imagePathToId[$data['image_base']])) {
                    $product->image_base = $imagePathToId[$data['image_base']];
                }
                if (isset($imagePathToId[$data['image_listing']])) {
                    $product->image_listing = $imagePathToId[$data['image_listing']];
                }
                if (isset($imagePathToId[$data['image_thumbnail']])) {
                    $product->image_thumbnail = $imagePathToId[$data['image_thumbnail']];
                }
                $product->save();
            }

            // Description
            $descriptions = array();
            foreach ($this->_getCsvProductTitles('description') as $field) {
                $fieldValues = Zend_Json_Decoder::decode($data[$field]);
                if (!is_array($fieldValues)) {
                    continue;
                }
                foreach ($fieldValues as $value) {
                    $langCode = key($value);
                    $value = current($value);
                    if (!isset($languages[$langCode])) {
                        continue;
                    }
                    $descriptions[$languages[$langCode]][$field] = $value;
                }
            }
            $product->setDescription($descriptions);

            // stock
            $stock = Axis::single('catalog/product_stock')->getRow($product->id);
            $stock->in_stock = $data['in_stock'];
            $stock->save();

            // Attributes
            $mAttribute = Axis::single('catalog/product_attribute');

            // Variations
            if (!empty($data['variations'])) {
                $variations = Zend_Json_Decoder::decode($data['variations']);
                $mVariation = Axis::single('catalog/product_variation');
                $mVariation->delete('product_id = ' . $product->id);
                foreach ($variations as &$variation) {
                    $pVariation = $mVariation->createRow();
                    $pVariation->product_id = $product->id;
                    $pVariation->sku = $variation['sku'];
                    list($pVariation->price,
                         $pVariation->price_type) = $this->_valueToModifier($variation['price']);
                    list($pVariation->weight,
                         $pVariation->weight_type) = $this->_valueToModifier($variation['weight']);
                    $variation['id'] = $pVariation->save();

                    // insert new attributes
                    foreach (array_slice($variation, 3) as $optionName => $valueText) {
                        $option = $this->_getCreateOption($optionName, $createOptions);

                        // if option does not exists or valueset is empty then skip this attribute
                        // because variation can contain only selectable options
                        if (!$option || !$option->valueset_id) {
                            continue;
                        }
                        // the same, values
                        $value = $this->_getCreateValue($valueText, $option->valueset_id, $createOptions);
                        if (!$value) {
                            continue;
                        }

                        $mAttribute->insert(array(
                            'product_id'      => $productId,
                            'variation_id'    => $variation['id'],
                            'option_id'       => $option->id,
                            'option_value_id' => $value->id
                        ));
                    }
                }
            }

            $optionValues = array_slice($data, $optionsIndexStartFrom);
            $i = 0; // used as key for creating new attributes-modifiers
            $mAttribute->delete('variation_id = 0 AND product_id = ' . $productId);
            while (list($optionName, $optionValue) = each($optionValues)) {
                if (is_array($optionValue)) {
                    $modifier = $optionValue;
                    $optionValue = $modifier['value'];
                    $optionName = $modifier['optionName'];
                } else {
                    $modifier = false;
                }

                $optionValue = trim($optionValue);
                $option = $this->_getCreateOption($optionName, $createOptions);
                if (!$option
                    || ($optionTypes[$option->input_type] != $this->_optionNameToType($optionName))
                    || empty($optionValue)) {

                    // if option not exists or type is wrong then skip this option
                    continue;
                }

                /* check is this modifier json data */
                if (false !== strpos($optionValue, '[{"') && false !== strpos($optionValue, '"}]')) {
                    $modifiers = Zend_Json_Decoder::decode($optionValue);
                    foreach ($modifiers as $modifier) {
                        $modifier['optionName'] = $optionName;
                        if (empty($modifier['value'])) {
                            $modifier['value'] = current(explode(',', $optionName));
                        }
                        $optionValues['modifier_' . $i++] = $modifier;
                    }
                    continue;
                }

                if (!$option->isInputable() && $option->valueset_id) {
                    if (!$value = $this->_getCreateValue($optionValue, $option->valueset_id, $createOptions)) {
                        continue;
                    }
                    $valueId = $value->id;
                } else {
                    $valueId = new Zend_Db_Expr('NULL');
                }

                $attribute = $mAttribute->createRow();
                $attribute->product_id = $product->id;
                $attribute->option_id = $option->id;
                $attribute->option_value_id = $valueId;
                if ($modifier) {
                    list($attribute->price,
                         $attribute->price_type) = $this->_valueToModifier($modifier['price']);
                    list($attribute->weight,
                         $attribute->weight_type) = $this->_valueToModifier($modifier['weight']);
                    $attribute->modifier = 1;
                }

                $attribute->save();
            }
        }
        return $log;
    }
}
