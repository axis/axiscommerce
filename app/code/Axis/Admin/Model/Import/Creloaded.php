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
 * @subpackage  Axis_Admin_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Import_Creloaded extends Axis_Admin_Model_Import_Abstract
{
    private $_priority_queue = array(
        'Categories'      => 'category',
        'Manufacturers'   => 'manufacturer',
        //'Tax rates'       => 'taxrate',
        'Product attributes' => 'productAttribute',
        'Product extra fields' => 'productExtraField',
        'Products'        => 'product',
        'Customers'       => 'customer',
        'Orders'          => 'order'
    );

    /* oscommerce_type_id => axis_type_id
     *
     * @var const array
     */
    private $_option_types = array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4
    );

    private $_sku = null;

    /**
     *
     * @param array $data
     * @return <type>
     */
    public static function getInstance($data) {
        parent::$__CLASS__ = __CLASS__;
        return parent::getInstance($data);
    }

    public function dispose()
    {
        $this->clearSession();
        parent::dispose();
    }

    private function clearSession()
    {
        unset($_SESSION['processed_count']);
        unset($_SESSION['imported_count']);
        unset($_SESSION['category_relations_array']);
        unset($_SESSION['manufacturer_relations_array']);
        unset($_SESSION['option_relations_array']);
        unset($_SESSION['option_value_relations_array']);
        unset($_SESSION['extrafield_relations_array']);
        unset($_SESSION['product_relations_array']);
        unset($_SESSION['customer_relations_array']);
        unset($_SESSION['status_relations_array']);
        unset($_SESSION['message_stack']);
    }

    public function getQueue()
    {
        return $this->_priority_queue;
    }

    /**
     * @param string $group
     * @param array $language.
     *      Example: array([1] => [3])
     *      /language with id = 3 from oscommerce
     *      will be written to language 1 in axis
     * @param int $site
     * @param int $primary_language
     * @return array([completed_group] => boolean, [imported] => integer)
     */
    public function import($group, $language, $site, $primary_language)
    {
        $this->_language = $language;
        $this->_site = $site;
        $this->_primary_language = $primary_language;
        $this->_root = Axis::single('catalog/category')->getRoot($site)->id;

        if (!isset($_SESSION['processed_count']) || $_SESSION['processed_count'] == 0) {
            $_SESSION['processed_count'] = 0;
            $_SESSION['imported_count'] = 0;
            if (!isset($_SESSION['message_stack']))
                $_SESSION['message_stack'] = array();
            foreach ($_SESSION['message_stack'] as &$stack) {
                $stack = array();
            }
            if ($group == 'category')
                $_SESSION['category_relations_array'] = array(0 => 0);
            else if ($group == 'manufacturer')
                $_SESSION['manufacturer_relations_array'] = array();
            else if ($group == 'productAttribute') {
                $_SESSION['option_relations_array'] = array();
                $_SESSION['option_value_relations_array'] = array();
            } else if ($group == 'productExtraField')
                $_SESSION['extrafield_relations_array'] = array();
            else if ($group == 'product')
                $_SESSION['product_relations_array'] = array();
            else if ($group == 'customer')
                $_SESSION['customer_relations_array'] = array();
            else if ($group == 'order')
                $_SESSION['status_relations_array'] = array();
        }

        $result = $this->importGroup(ucfirst($group));
        if ($result['completed_group']) {
            unset($_SESSION['processed_count']);
            unset($_SESSION['imported_count']);
        }

        return $result;
    }

    public function getLanguages()
    {
        $query = "SELECT l.*
                  FROM " . parent::$_table_prefix . "languages AS l";

        return parent::$_adapter->fetchAll($query);
    }

    public function importGroup($group)
    {
        $time_start_script = time();

        $time_export = 0;
        $completed_group = false;

        while (true) {
            $time_start_item = time();

            $entry = call_user_func(array($this, 'get'.$group));

            if (!$entry) {
               $completed_group = true;
               break;
            }

            call_user_func(array($this, 'add'.$group), $entry);

            $time_end_item = time();
            $time_sript = $time_end_item - $time_start_script;
            $time_export_item = max($time_end_item - $time_start_item, $time_export);
            if (($time_sript + $time_export_item) > 20) {
                break;
            }
        }

        return array(
            'completed_group' => $completed_group,
            'group'           => $group,
            'processed'       => $_SESSION['processed_count'],
            'imported'        => $_SESSION['imported_count'],
            'messages'        => $_SESSION['message_stack']
        );
    }

    private function addCategory($entry)
    {
        $image = '';
        if ($entry['category']['categories_image'] != '') {
            $image = parent::_copyFile(
                parent::$_image_path . '/import/' . $entry['category']['categories_image'],
                parent::$_image_path . '/category'
            );
        }

        $data = array(
            'status'        => 'enabled',
            'created_on'    => $entry['category']['date_added'],
            'modified_on'   => $entry['category']['last_modified'],
            'image_base'    => $image,
            'image_listing' => $image
        );

        if ($entry['category']['parent_id'] == 0) {
            $insertedId = Axis::single('catalog/category')->insertItem($data, $this->_root);
        } else {
            $parentId = $_SESSION['category_relations_array'][$entry['category']['parent_id']];
            $insertedId = Axis::single('catalog/category')->insertItem($data, $parentId);
        }

        $_SESSION['imported_count']++;
        $_SESSION['processed_count']++;
        $_SESSION['category_relations_array'][$entry['category']['categories_id']] = $insertedId;

        $keyWordTmp = $keyWord = 'category';

        if (isset($entry['description'])
            && isset($entry['description'][$this->_primary_language])) {

            $keyWordTmp = $keyWord = $this->_prepareString(
                $entry['description'][$this->_primary_language]['categories_name']
            );
            $_SESSION['message_stack']['successfully imported'][] =
                $entry['description'][$this->_primary_language]['categories_name'];
        }

        $i = 0;
        while (Axis::single('catalog/hurl')->hasDuplicate($keyWord, $this->_site)) {
            $keyWord = $keyWordTmp . '-' . ++$i;
        }

        Axis::single('catalog/hurl')->insert(array(
            'key_word'  => $keyWord,
            'site_id'   => $this->_site,
            'key_type'  => 'c',
            'key_id'    => $insertedId
        ));

        foreach ($this->_language as $axisLanguage => $creloadedLanguage) {
            if (!isset($entry['description']) || !isset($entry['description'][$creloadedLanguage])) {
                continue;
            }

            Axis::single('catalog/category_description')->insert(array(
                'category_id' => $insertedId,
                'language_id' => $axisLanguage,
                'name'        => empty($entry['description'][$creloadedLanguage]['categories_name']) ?
                    '' : $entry['description'][$creloadedLanguage]['categories_name'],
                'description' => empty($entry['description'][$creloadedLanguage]['categories_description']) ?
                    '' : $entry['description'][$creloadedLanguage]['categories_description'],
                'meta_title'  => empty($entry['description'][$creloadedLanguage]['categories_head_title_tag']) ?
                    '' : $entry['description'][$creloadedLanguage]['categories_head_title_tag'],
                'meta_description' => empty($entry['description'][$creloadedLanguage]['categories_head_desc_tag']) ?
                    '' : $entry['description'][$creloadedLanguage]['categories_head_desc_tag'],
                'meta_keyword' => empty($entry['description'][$creloadedLanguage]['categories_head_keywords_tag']) ?
                    '' : $entry['description'][$creloadedLanguage]['categories_head_desc_tag']
            ));
        }
    }

    private function addManufacturer($entry)
    {
        $image = '';

        if (!empty($entry['manufacturer']['manufacturers_image'])) {
            $image = parent::_copyFile(
                parent::$_image_path . '/import/' . $entry['manufacturer']['manufacturers_image'],
                parent::$_image_path . '/manufacturer'
            );
        }

        $data = array(
            'name'  => $entry['manufacturer']['manufacturers_name'],
            'image' => $image
        );

        $duplicate = Axis::db()->fetchOne(
            "SELECT m.id
            FROM " . parent::$_db_prefix . parent::MANUFACTURER . " AS m
            WHERE m.name = '$data[name]'"
        );

        if (!$duplicate) {
            $manufacturerId = Axis::single('catalog/product_manufacturer')->insert($data);

            foreach ($this->_language as $axisLanguage => $oscLanguage) {
                Axis::single('catalog/product_manufacturer_title')->insert(array(
                    'manufacturer_id' => $manufacturerId,
                    'language_id'     => $axisLanguage,
                    'title'           => $data['name']
                ));
            }

            $_SESSION['imported_count']++;
            $_SESSION['message_stack']['successfully imported'][] = $data['name'];

            //human url
            $keyWord = $data['name'] = $this->_prepareString($data['name']);
            $i = 0;
            while (Axis::single('catalog/hurl')->hasDuplicate($keyWord, $this->_site)) {
                $keyWord = $data['name'] . '-' . ++$i;
            }

            Axis::single('catalog/hurl')->insert(array(
                'key_word'  => $keyWord,
                'site_id'   => $this->_site,
                'key_type'  => 'm',
                'key_id'    => $manufacturerId
            ));
        } else {
            $manufacturerId = $duplicate;
            $_SESSION['message_stack']['skipped (duplicate entry)'][] = $data['name'];
        }

        $_SESSION['processed_count']++;
        $_SESSION['manufacturer_relations_array'][$entry['manufacturer']['manufacturers_id']] = $manufacturerId;
    }

    private function addProduct($entry)
    {
        if (isset($_SESSION['manufacturer_relations_array']) && $entry['product']['manufacturers_id'] != 0) {
            $manufacturer = isset($_SESSION['manufacturer_relations_array'][$entry['product']['manufacturers_id']]) ?
                $_SESSION['manufacturer_relations_array'][$entry['product']['manufacturers_id']] : 0;
        } else {
            $manufacturer = new Zend_Db_Expr('NULL');
        }

        if (isset($_SESSION['taxclass_relations_array'])) {
            $tax_class = new Zend_Db_Expr('NULL'); //@todo import tax rates
        } else {
            $tax_class = new Zend_Db_Expr('NULL');
        }

        $date = Axis_Date::now()->toPhpString("Y-m-d H:i:s");

        $images = array();
        $images = array_filter(array_keys($entry['product']), array($this, "isImage"));

        $image_path = array();
        foreach ($images as $column_name) {
            if (!empty($entry['product'][$column_name])) {
                $image_path[$column_name] = $this->_copyFile(
                    parent::$_image_path . '/import/' . $entry['product'][$column_name],
                    parent::$_image_path . '/product'
                );
            }
        }

        //get overall viewed count
        $viewed = 0;
        foreach ($entry['description'] as $lang_id => $description) {
            $viewed += intval($description['products_viewed']);
        }

        //getting sku number
        if (!is_numeric($this->_sku)) {
            $sku_query = "SELECT max(p.id)
                FROM " . parent::$_db_prefix . parent::PRODUCT . " AS p";

            $this->_sku = Axis::db()->fetchOne($sku_query);
        }

        $product = array(
            'manufacturer_id'   => $manufacturer,
            'quantity'          => (empty($entry['product']['products_quantity']) || $entry['product']['products_quantity'] < 0) ?
                0 : $entry['product']['products_quantity'],
            'sku'               => 'CRE Loaloded Store - ' . ++$this->_sku,
            'image_base'        => null,
            'image_listing'     => null,
            'image_thumbnail'   => null,
            'price'             => $entry['product']['products_price'],
            'date_available'    => $date,
            'weight'            => $entry['product']['products_weight'],
            'is_active'         => 1,
            'ordered'           => $entry['product']['products_ordered'],
            'created_on'        => $date,
            'modified_on'       => $date,
            'tax_class_id'      => $tax_class,
            'viewed'            => $viewed
        );

        $product_id = Axis::single('catalog/product')->insert($product);

        $product_stock = array(
            'product_id'    => $product_id,
            'in_stock'      => $entry['product']['products_status'],
            'manage'        => 1,
            'min_qty'       => 1,
            'min_qty_allowed' => 1,
            'max_qty_allowed' => 0,
            'decimal'       => 0,
            'notify_qty'    => 0,
            'backorder'     => 0
        );
        Axis::db()->insert(parent::$_db_prefix . parent::PRODUCT_STOCK, $product_stock);

        foreach (array_keys($this->_language) as $axis_language) {
            if (!isset($entry['description']) || !isset($entry['description'][$this->_language[$axis_language]]))
                continue;

            $head_title = empty($entry['description'][$this->_language[$axis_language]]['products_head_title_tag']) ?
                '' : $entry['description'][$this->_language[$axis_language]]['products_head_title_tag'];
            $head_description = empty($entry['description'][$this->_language[$axis_language]]['products_head_desc_tag']) ?
                '' : $entry['description'][$this->_language[$axis_language]]['products_head_desc_tag'];
            $head_keywords = empty($entry['description'][$this->_language[$axis_language]]['products_head_keywords_tag']) ?
                '' : $entry['description'][$this->_language[$axis_language]]['products_head_keywords_tag'];
            $description = empty($entry['description'][$this->_language[$axis_language]]['products_description']) ?
                '' : $entry['description'][$this->_language[$axis_language]]['products_description'];

            //product description
            $description = array(
                'product_id'        => $product_id,
                'language_id'       => $axis_language,
                'name'              => $entry['description'][$this->_language[$axis_language]]['products_name'],
                'description'       => $description,
                'viewed'            => $entry['description'][$this->_language[$axis_language]]['products_viewed'],
                'image_seo_name'    => $this->_prepareString($entry['description'][$this->_language[$axis_language]]['products_name']),
                'meta_title'        => $head_title,
                'meta_description'  => $head_description,
                'meta_keyword'      => $head_keywords,
                'short_description' => $description
            );

            Axis::db()->insert(
                parent::$_db_prefix . parent::PRODUCT_DESCRIPTION, $description
            );
        }

        //human url
        $key_word = "";
        if (!empty($entry['description'][$this->_primary_language]['products_url'])) {
            $key_word = $this->_prepareString($entry['description'][$this->_primary_language]['products_url']);
        } elseif (!empty($entry['description'][$this->_primary_language]['products_name'])) {
            $key_word = $this->_prepareString($entry['description'][$this->_primary_language]['products_name']);
        }

        if (!empty($key_word)) {
            $key_word = $product['sku'];
        }

        $i = 0;
        $uniqueKeyWord = $key_word;
        while (Axis::model('catalog/hurl')->hasDuplicate($uniqueKeyWord, $this->_site)) {
            $uniqueKeyWord = $key_word . '-' . $i++;
        }

        $hurl = array (
            'key_word'  => $uniqueKeyWord,
            'site_id'   => $this->_site,
            'key_type'  => 'p',
            'key_id'    => $product_id
        );

        Axis::db()->insert(parent::$_db_prefix . parent::HUMAN_URL, $hurl);

        //additional images
        $imagePathToId = array();
        foreach ($image_path as $path) {
            if (isset($imagePathToId[$path])) {
                continue;
            }
            $imagePathToId[$path] = Axis::single('catalog/product_image')->insert(array(
                'product_id' => $product_id,
                'path'       => $path,
                'sort_order' => 10
            ));
        }
        $productRow = Axis::single('catalog/product')->find($product_id)->current();
        if (isset($image_path['products_image_med'])
            && isset($imagePathToId[$image_path['products_image_med']])) {

            $productRow->image_base = $imagePathToId[$image_path['products_image_lrg']];
        }
        if (isset($image_path['products_image'])
            && isset($imagePathToId[$image_path['products_image']])) {

            $productRow->image_listing   = $imagePathToId[$image_path['products_image']];
            $productRow->image_thumbnail = $imagePathToId[$image_path['products_image']];
        }
        $productRow->save();

        //product_to_category relations
        if (isset($_SESSION['category_relations_array'])) {
            foreach ($entry['categories'] as $category_id) {
                if (isset($_SESSION['category_relations_array'][$category_id]))
                    Axis::db()->insert(
                        parent::$_db_prefix . parent::PRODUCT_TO_CATEGORIES, array(
                            'category_id' =>  $_SESSION['category_relations_array'][$category_id],
                            'product_id' => $product_id
                        )
                    );
            }
        }

        //product attributes (modifiers)
        if (isset($_SESSION['option_relations_array']) && isset($_SESSION['option_value_relations_array'])) {
            foreach ($entry['attributes'] as $attribute) {

                if (!isset($_SESSION['option_relations_array'][$attribute['options_id']])) {
                    continue;
                }

                $option_value_id = isset($_SESSION['option_value_relations_array'][$attribute['options_values_id']]) ?
                    $_SESSION['option_value_relations_array'][$attribute['options_values_id']] : new Zend_Db_Expr('NULL');

                Axis::db()->insert(
                    parent::$_db_prefix . parent::PRODUCT_ATTRIBUTE, array(
                        'variation_id'  => 0,
                        'product_id'    => $product_id,
                        'option_id'     => $_SESSION['option_relations_array'][$attribute['options_id']],
                        'option_value_id' => $option_value_id,
                        'price'         => $attribute['price_prefix'] . $attribute['options_values_price'],
                        'price_type'    => 'by',
                        'weight'        => 0,
                        'weight_type'   => 'to',
                        'modifier'      => 1
                    )
                );
            }
        }

        //product attributes (properties)
        if (isset($_SESSION['extrafield_relations_array'])) {
            foreach ($entry['extra_fields'] as $field) {

                if (!isset($_SESSION['extrafield_relations_array'][$field['products_extra_fields_id']])) {
                    continue;
                }

                Axis::db()->insert(
                    parent::$_db_prefix . parent::PRODUCT_ATTRIBUTE, array(
                        'variation_id' => '0',
                        'product_id'   => $product_id,
                        'option_id'    => $_SESSION['extrafield_relations_array'][$field['products_extra_fields_id']],
                        'option_value_id' => new Zend_Db_Expr('NULL'),
                        'price' => '0',
                        'price_type' => 'by',
                        'weight' => '0',
                        'weight_type' => 'to',
                        'modifier' => '0'
                    )
                );
                $attribute_id = Axis::db()->lastInsertId(parent::PRODUCT_ATTRIBUTE);

                foreach (array_keys($this->_language) as $axis_language) {
                    Axis::db()->insert(
                        parent::PRODUCT_ATTRIBUTE_VALUE, array(
                            'product_attribute_id' => $attribute_id,
                            'language_id' => $axis_language,
                            'attribute_value' => $field['products_extra_fields_value']
                        )
                    );
                }
            }
        }

        $_SESSION['imported_count']++;
        $_SESSION['processed_count']++;
        $_SESSION['product_relations_array'][$entry['product']['products_id']] = $product_id;
        $_SESSION['message_stack']['successfully imported'][] = $key_word;
    }

    private function addCustomer($entry)
    {
        $date = Axis_Date::now()->toPhpString("Y-m-d H:i:s");

        $duplicate = Axis::db()->fetchOne(
            "SELECT c.id
            FROM " . parent::$_db_prefix . parent::CUSTOMER . " AS c
            WHERE c.email = ?",
            $entry['customer']['customers_email_address']
        );

        if (!$duplicate) {
            $customer = array(
                'email' => $entry['customer']['customers_email_address'],
                'password' => $entry['customer']['customers_password'],
                'site_id' => $this->_site,
                'is_active' => 1,
                'default_shipping_address_id' => '',
                'default_billing_address_id' => '',
                'created_at' => $date,
                'modified_at' => $date,
                'group_id' => 1
            );
            Axis::db()->insert(parent::$_db_prefix . parent::CUSTOMER, $customer);
            $customer_id = Axis::db()->lastInsertId(parent::$_db_prefix . parent::CUSTOMER);

            $_SESSION['imported_count']++;
            $_SESSION['customer_relations_array'][$entry['customer']['customers_id']] = $customer_id;
            $_SESSION['message_stack']['successfully imported'][] = $entry['customer']['customers_email_address'];

            $address_relations_array = array();

            foreach ($entry['addresses'] as $address) {
                $data = array(
                    'customer_id' => $customer_id,
                    'gender' => $address['entry_gender'],
                    'company' => $address['entry_company'],
                    'phone' => $address['entry_telephone'],
                    'fax' => $address['entry_fax'],
                    'firstname' => $address['entry_firstname'],
                    'lastname' => $address['entry_lastname'],
                    'street_address' => $address['entry_street_address'],
                    'suburb' => $address['entry_suburb'],
                    'postcode' => $address['entry_postcode'],
                    'city' => $address['entry_city'],
                    'state'=> $address['entry_state'],
                    'country_id' => $address['entry_country_id'],
                    'zone_id' => $address['entry_zone_id']
                );
                Axis::db()->insert(parent::$_db_prefix . parent::CUSTOMER_ADDRESS, $data);
                $address_id = Axis::db()->lastInsertId(parent::$_db_prefix . parent::CUSTOMER_ADDRESS);

                $address_relations_array[$address['address_book_id']] = $address_id;
            }

            Axis::db()->update(parent::$_db_prefix . parent::CUSTOMER, array(
                'default_shipping_address_id' => $address_relations_array[$entry['customer']['customers_default_address_id']],
                'default_billing_address_id'  => $address_relations_array[$entry['customer']['customers_default_address_id']]
            ));

        } else {
            if (isset($_SESSION['customer_relations_array'][$entry['customer']['customers_id']]))
                unset($_SESSION['customer_relations_array'][$entry['customer']['customers_id']]);
            $_SESSION['message_stack']['skipped (duplicate entry)'][] = $entry['customer']['customers_email_address'];
        }

        $_SESSION['processed_count']++;
    }

    private function addProductAttribute($entry)
    {
        $valueset_id = new Zend_Db_Expr('NULL');

        $_SESSION['processed_count']++;

        if (!isset($entry['option_text'][$this->_primary_language]['products_options_name']) ||
            $entry['option_text'][$this->_primary_language]['products_options_name'] == '') {
            $_SESSION['message_stack']['skipped (attribute code is undefined)'][] = 'CreLoaded option id: ' . $entry['option']['products_options_id'];
            return;
        }

        // create valueset from option_values
        if (count($entry['option_values'])) {

            $duplicate_valueset = Axis::db()->fetchOne(
                "SELECT pov.id
                FROM " . parent::$_db_prefix . parent::PRODUCT_OPTION_VALUESET . " AS pov
                WHERE pov.name = ? ", $entry['option_text'][$this->_primary_language]['products_options_name']
            );

            if (!$duplicate_valueset) {
                Axis::db()->insert(
                    parent::$_db_prefix . parent::PRODUCT_OPTION_VALUESET, array(
                        'name' => $entry['option_text'][$this->_primary_language]['products_options_name']
                    )
                );
                $valueset_id = Axis::db()->lastInsertId(parent::$_db_prefix . parent::PRODUCT_OPTION_VALUESET);
            } else {
                $valueset_id = $duplicate_valueset;
            }

            foreach (array_keys($this->_language) as $axis_language) {
                $i = 0;
                foreach ($entry['option_values'][$this->_language[$axis_language]] as $value) {

                    $duplicate_option_value = Axis::db()->fetchOne(
                        "SELECT povt.option_value_id
                        FROM " . parent::$_db_prefix . parent::PRODUCT_OPTION_VALUE_TEXT . " AS povt
                        WHERE povt.name = ?",
                        $entry['option_values'][$this->_primary_language][$i++]['products_options_values_name']
                    );

                    if (!$duplicate_option_value) {
                        //inserting product_option_value
                        Axis::db()->insert(parent::$_db_prefix . parent::PRODUCT_OPTION_VALUE, array(
                            'sort_order' => '0',
                            'valueset_id' => $valueset_id
                        ));
                        $option_value_id = Axis::db()->lastInsertId(parent::$_db_prefix . parent::PRODUCT_OPTION_VALUE);
                    }  else {
                        $option_value_id = $duplicate_option_value;
                    }

                    $name = $value['products_options_values_name'];

                    $duplicate_option_value_text = Axis::db()->fetchOne(
                        "SELECT povt.option_value_id
                        FROM " . parent::$_db_prefix . parent::PRODUCT_OPTION_VALUE_TEXT . " AS povt
                        WHERE povt.language_id = $axis_language
                        AND povt.option_value_id = $option_value_id"
                    );

                    if (!$duplicate_option_value_text) {
                        //inserting product_option_value_text
                        Axis::db()->insert(
                            parent::$_db_prefix . parent::PRODUCT_OPTION_VALUE_TEXT, array(
                                'option_value_id' => $option_value_id,
                                'language_id' => $axis_language,
                                'name' => isset($name) ? $name : ''
                            )
                        );
                    }

                    $_SESSION['option_value_relations_array'][$value['products_options_values_id']] = $option_value_id;
                }
            }
        }

        $duplicate_option = Axis::db()->fetchRow(
            "SELECT po.id, po.valueset_id
            FROM " . parent::$_db_prefix . parent::PRODUCT_OPTION . " AS po
            WHERE po.code = ?",
            $this->_prepareString($entry['option_text'][$this->_primary_language]['products_options_name'], '_')
        );

        //if same option exist and its valueset == our valueset => using it, else create new;
        if (!$duplicate_option || ($duplicate_option['valueset_id'] != $valueset_id)) {

            $code = $this->_prepareString($entry['option_text'][$this->_primary_language]['products_options_name'], '_');

            if ($duplicate_option)
                $code .= $valueset_id;

            //insert product_option
            Axis::db()->insert(
                parent::$_db_prefix . parent::PRODUCT_OPTION, array(
                    'code' => $code,
                    'input_type' => $this->_option_types[$entry['option']['options_type']],
                    'sort_order'  => $entry['option']['products_options_sort_order'],
                    'searchable'  => 1,
                    'comparable'  => 1,
                    'languagable' => 1,
                    'filterable'  => 1,
                    'valueset_id' => $valueset_id
                )
            );
            $option_id = Axis::db()->lastInsertId(parent::$_db_prefix . parent::PRODUCT_OPTION);

            //insert product_option_text
            foreach(array_keys($this->_language) as $axis_language) {
                Axis::db()->insert(
                    parent::$_db_prefix . parent::PRODUCT_OPTION_TEXT, array(
                        'option_id' => $option_id,
                        'language_id' => $axis_language,
                        'name' => isset($entry['option_text'][$this->_language[$axis_language]]) ? $entry['option_text'][$this->_language[$axis_language]]['products_options_name'] : '',
                        'description' => isset($entry['option_text'][$this->_language[$axis_language]]) ? $entry['option_text'][$this->_language[$axis_language]]['products_options_instruct'] : ''
                    )
                );
            }

            $_SESSION['imported_count']++;
            $_SESSION['message_stack']['successfully imported'][] = $entry['option_text'][$this->_primary_language]['products_options_name'];

        } else {
            $option_id = $duplicate_option['id'];
            $_SESSION['message_stack']['skipped (duplicate entry)'][] = $entry['option_text'][$this->_primary_language]['products_options_name'];
        }

        $_SESSION['option_relations_array'][$entry['option']['products_options_id']] = $option_id;
    }

    private function addProductExtraField($entry)
    {
        $code = $this->_prepareString($entry['extra_field']['products_extra_fields_name'], '_');

        $duplicate_option = Axis::db()->fetchOne(
            "SELECT po.id
            FROM " . parent::$_db_prefix . parent::PRODUCT_OPTION . " AS po
            WHERE po.code = ? ", $code
        );

        if ($duplicate_option) {
            $option_id = $duplicate_option;
            $_SESSION['message_stack']['skipped (duplicate entry)'][] = $entry['extra_field']['products_extra_fields_name'];
        } else {
            Axis::db()->insert(
                parent::$_db_prefix . parent::PRODUCT_OPTION, array(
                    'code' => $code,
                    'input_type' => 4,
                    'sort_order'  => 5,
                    'searchable'  => 1,
                    'comparable'  => 1,
                    'languagable' => 0,
                    'filterable'  => 0,
                    'valueset_id' => new Zend_Db_Expr('NULL')
                )
            );
            $option_id = Axis::db()->lastInsertId(parent::$_db_prefix . parent::PRODUCT_OPTION);

            //insert product_option_text
            foreach(array_keys($this->_language) as $axis_language) {
                Axis::db()->insert(
                    parent::$_db_prefix . parent::PRODUCT_OPTION_TEXT, array(
                        'option_id' => $option_id,
                        'language_id' => $axis_language,
                        'name' => $entry['extra_field']['products_extra_fields_name'],
                        'description' => ''
                    )
                );
            }

            $_SESSION['imported_count']++;
            $_SESSION['message_stack']['successfully imported'][] = $entry['extra_field']['products_extra_fields_name'];
        }

        $_SESSION['extrafield_relations_array'][$entry['extra_field']['products_extra_fields_id']] = $option_id;
        $_SESSION['processed_count']++;
    }

    private function addOrder($entry)
    {
        $_SESSION['processed_count']++;

        if (!isset($_SESSION['customer_relations_array'][$entry['order']['customers_id']])) {
            $_SESSION['message_stack']['skipped (customer not found)'][] = $entry['order']['customers_name'] . ' ' . $entry['order']['customers_email_address'];
            return;
        }

        /**
         * array(
         *  [0] => array(
         *    [lang_id] => array(values)
         *  ),
         *  [1] => ...
         * )
         */
        foreach ($entry['statuses'] as $id => $status) {
            if (!isset($status[$this->_primary_language]) || $status[$this->_primary_language] == '') {
                $current_status = current($status);
                $_SESSION['message_stack']['skipped (status code is undefined)'][] = 'CreLoaded status id: ' . $id;
                continue;
            }

            if (!isset($_SESSION['status_relations_array'][$id])) {
                $duplicate = Axis::db()->fetchOne(
                    "SELECT os.id
                    FROM " . parent::$_db_prefix . parent::ORDER_STATUS . " AS os
                    WHERE name = ?",
                    $status[$this->_primary_language]
                );

                if (!$duplicate) { //insert new status
                    $status_id = Axis::single('sales/order_status')->insert(array(
                        'name' => $status[$this->_primary_language],
                        'system' => false
                    ));;
                    foreach(array_keys($this->_language) as $axis_language) {
                        Axis::single('sales/order_status_text')->insert(array(
                            'status_id' => $status_id,
                            'language_id' => $axis_language,
                            'status_name' => $status[$this->_language[$axis_language]]
                        ));
                    }
                } else { //use existing status
                    $status_id = $duplicate;
                    $_SESSION['message_stack']['skipped statuses (duplicate entry)'][] = $status[$this->_primary_language];
                }

                $_SESSION['status_relations_array'][$id] = $status_id;
            }
        }

        $total = 0;
        $tax = new Zend_Db_Expr('NULL');
        //get order total
        foreach ($entry['total'] as $key => $line) {
            if ($line['title'] == 'Total:')
                $total = $line['value'];
            if ($line['title'] == 'Tax:')
                $tax = $line['value'];
        }

        $order = array(
            'customer_id' => $_SESSION['customer_relations_array'][$entry['order']['customers_id']],
            'customer_email' => $entry['order']['customers_email_address'],
            'delivery_firstname' => $entry['order']['delivery_name'],
            'delivery_lastname' => $entry['order']['delivery_name'],
            'delivery_phone' => '',
            'delivery_fax' => '',
            'delivery_company' => $entry['order']['delivery_company'],
            'delivery_street_address' => $entry['order']['delivery_street_address'],
            'delivery_suburb' => $entry['order']['delivery_suburb'],
            'delivery_city' => $entry['order']['delivery_city'],
            'delivery_postcode' => $entry['order']['delivery_postcode'],
            'delivery_state' => $entry['order']['delivery_state'],
            'delivery_country' => $entry['order']['delivery_country'],
            'delivery_address_format_id' => $entry['order']['delivery_address_format_id'],
            'billing_firstname' => $entry['order']['billing_name'],
            'billing_lastname' => $entry['order']['billing_name'],
            'billing_phone' => '',
            'billing_fax' => '',
            'billing_company' => $entry['order']['billing_company'],
            'billing_street_address' => $entry['order']['billing_street_address'],
            'billing_suburb' => $entry['order']['billing_suburb'],
            'billing_city' => $entry['order']['billing_city'],
            'billing_postcode' => $entry['order']['billing_postcode'],
            'billing_state' => $entry['order']['billing_state'],
            'billing_country' => $entry['order']['billing_country'],
            'billing_address_format_id' => $entry['order']['billing_address_format_id'],
            'payment_method' => $entry['order']['payment_method'],
            'payment_method_code' => $this->_prepareString($entry['order']['payment_method'], '', true) . '_Standard',
            'shipping_method' => 'Free Shipping',
            'shipping_method_code' => 'Free_Standard',
            'coupon_code' => new Zend_Db_Expr('NULL'),
            //'cc_type' => $entry['order']['cc_type'],
            //'cc_owner' => $entry['order']['cc_owner'],
            //'cc_number' => $entry['order']['cc_number'],
            //'cc_expires' => $entry['order']['cc_expires'],
            //'cc_cvv' => $entry['order']['cc_ccv'],
            'date_modified_on' => new Zend_Db_Expr('NULL'),
            'date_purchased_on' => $entry['order']['date_purchased'],
            'date_finished_on' => new Zend_Db_Expr('NULL'),
            'order_status_id' => $_SESSION['status_relations_array'][$entry['order']['orders_status']],
            'currency' => $entry['order']['currency'],
            'currency_rate' => $entry['order']['currency_value'],
            'order_total' => $total,
            'txn_id' => 0,
            'ip_address' => $entry['order']['ipaddy'],
            'site_id' => $this->_site
        );

        $order_id = Axis::single('sales/order')->insert($order);

        foreach ($entry['total'] as $line) {
            if ($line['title'] == 'Total:')
                continue;
            $title = substr($line['title'], 0, -1);
            $code =  preg_replace('/\s+/', '_', trim($title));
            $order_total = array(
                'order_id' => $order_id,
                'code'     => $code,
                'title'    => $title,
                'value'    => $line['value']
            );
            Axis::single('sales/order_total')->insert($order_total);
        }

        foreach ($entry['status_history'] as $date => $status) {
            $history_data = array(
                'order_id' => $order_id,
                'order_status_id' => $_SESSION['status_relations_array'][$status['orders_status_id']],
                'created_on' => $date,
                'notified' => $status['customer_notified'],
                'comments' => $status['comments']
            );
            Axis::single('sales/order_status_history')->insert($history_data);
        }

        foreach ($entry['products'] as $product) {

            //import product if not imported yet
            if (!isset($_SESSION['product_relations_array'][$product['product']['products_id']])) {
                $entry = $this->getProduct($product['product']['products_id']);
                $this->addProduct($entry);
            }

            $product_id = $_SESSION['product_relations_array'][$product['product']['products_id']];

            //get product sku
            $sku = Axis::single('catalog/product')->getSkuById($product_id);

            //inserting product
            $product_data = array(
                'order_id' => $order_id,
                'product_id' => $product_id,
                'variation_id' => new Zend_Db_Expr('NULL'),
                'sku' => $sku,
                'name' => $product['product']['products_name'],
                'price' => $product['product']['products_price'],
                'final_price' => $product['product']['final_price'],
                'tax' => $product['product']['products_tax'],
                'quantity' => $product['product']['products_quantity'],
                'backorder' => 0
            );

            $order_product_id = Axis::single('sales/order_product')->insert($product_data);

            if (!count($product['attributes']))
                continue;

            //inserting attributes
            foreach ($product['attributes'] as $attribute) {
                $attribute_data = array(
                    'order_product_id' => $order_product_id,
                    'product_option' => $attribute['products_options'],
                    'product_option_value' => $attribute['products_options_values']
                );

                Axis::db()->insert(parent::$_db_prefix . parent::ORDER_PRODUCT_ATTRIBUTE, $attribute_data);
            }
        }

        $_SESSION['imported_count']++;
    }

    /**
     * Get category row, according to $_SESSION['category_relations_array']
     *
     * @return array(category, description) if found, false - if not
     */
    private function getCategory()
    {
        $category = "SELECT c.*
            FROM " . parent::$_table_prefix . "categories AS c
            WHERE c.parent_id IN (" . implode(',', array_keys($_SESSION['category_relations_array'])) . ")
            AND   c.categories_id NOT IN (" . implode(',', array_keys($_SESSION['category_relations_array'])) . ")
            ORDER BY c.parent_id ASC, c.categories_id ASC
            LIMIT 1";

        $category = parent::$_adapter->fetchRow($category);

        if (!$category) {
            return false;
        }

        $description = "SELECT cd.language_id, cd.*
            FROM " . parent::$_table_prefix . "categories_description AS cd
            WHERE cd.categories_id = $category[categories_id]
            AND cd.language_id IN (" . implode(',', array_values($this->_language)) . ")";

        $description = parent::$_adapter->fetchAssoc($description);

        return array(
            'category' => $category,
            'description' => $description
        );
    }

    /**
     * Get manufacturer row, according to $_SESSION[processed_count]
     *
     * @return array(manufacturer)
     */
    private function getManufacturer()
    {
        $manufacturer = "SELECT m.*
                  FROM " . parent::$_table_prefix . "manufacturers AS m
                  ORDER BY m.manufacturers_id ASC
                  LIMIT $_SESSION[processed_count], 1";

        $manufacturer = parent::$_adapter->fetchRow($manufacturer);

        if (!$manufacturer)
            return false;

        return array(
            'manufacturer' => $manufacturer
        );
    }

    /**
     * Get product row, according to $_SESSION[processed_count]
     *
     * @return array(product, attributes, description) if found, otherwise - false
     */
    private function getProduct($product_id = null)
    {
        if (null === $product_id) {
            $product = "SELECT p.*
                FROM " . parent::$_table_prefix . "products AS p
                ORDER BY p.products_id ASC
                LIMIT $_SESSION[processed_count], 1";
        } else {
            $product = "SELECT p.*
                FROM " . parent::$_table_prefix . "products AS p
                WHERE p.products_id = $product_id";
        }

        $product = parent::$_adapter->fetchRow($product);

        if (!$product)
            return false;

        $description = "SELECT pd.language_id, pd.*
            FROM " . parent::$_table_prefix . "products_description AS pd
            WHERE pd.products_id = $product[products_id]
            AND pd.language_id IN (" . implode(',', array_values($this->_language)) . ")";

        $description = parent::$_adapter->fetchAssoc($description);

        $attributes = "SELECT pa.*
            FROM " . parent::$_table_prefix . "products_attributes AS pa
            WHERE pa.products_id = $product[products_id]";

        $attributes = parent::$_adapter->fetchAll($attributes);

        $categories = "SELECT ptc.categories_id
            FROM " . parent::$_table_prefix . "products_to_categories AS ptc
            WHERE ptc.products_id = $product[products_id]";

        $categories = parent::$_adapter->fetchCol($categories);

        $extra_field = "SELECT ptpef.*
            FROM " . parent::$_table_prefix . "products_to_products_extra_fields AS ptpef
            WHERE ptpef.products_id = $product[products_id]";

        $extra_field = parent::$_adapter->fetchAll($extra_field);

        return array(
            'product' => $product,
            'description' => $description,
            'attributes' => $attributes,
            'categories' => $categories,
            'extra_fields' => $extra_field
        );
    }

    private function getProductAttribute()
    {
        $option = "SELECT po.*
            FROM " . parent::$_table_prefix . "products_options AS po
            LIMIT $_SESSION[processed_count], 1";

        $option = parent::$_adapter->fetchRow($option);

        if (!$option)
            return false;

        $option_text = "SELECT pot.language_id, pot.*
            FROM " . parent::$_table_prefix . "products_options_text AS pot
            WHERE pot.products_options_text_id = $option[products_options_id]
            AND pot.language_id IN (" . implode(',', array_values($this->_language)) . ")";

        $option_text = parent::$_adapter->fetchAssoc($option_text);

        $option_value = "SELECT pov.language_id, pov.*
            FROM " . parent::$_table_prefix . "products_options_values AS pov
            INNER JOIN " . parent::$_table_prefix . "products_options_values_to_products_options
                AS povtpo ON povtpo.products_options_values_id = pov.products_options_values_id
            WHERE povtpo.products_options_id = $option[products_options_id]
            AND pov.language_id IN (" . implode(',', array_values($this->_language)) . ")";

        $option_value = parent::$_adapter->fetchAll($option_value);

        $option_values = array();

        foreach ($option_value as $value) {
            $option_values[$value['language_id']][] = $value;
        }

        return array(
            'option' => $option,
            'option_text' => $option_text,
            'option_values' => $option_values
        );
    }

    private function getProductExtraField()
    {
        $extra_field = "SELECT pef.*
            FROM " . parent::$_table_prefix . "products_extra_fields AS pef
            LIMIT $_SESSION[processed_count], 1";

        $extra_field = parent::$_adapter->fetchRow($extra_field);

        if (!$extra_field)
            return false;

        return array(
            'extra_field' => $extra_field
        );
    }

    private function getCustomer()
    {
        $customer = "SELECT c.*
            FROM " . parent::$_table_prefix . "customers AS c
            LIMIT $_SESSION[processed_count], 1";

        $customer = parent::$_adapter->fetchRow($customer);

        if (!$customer)
            return false;

        $addresses = "SELECT ab.address_book_id, ab.*
            FROM " . parent::$_table_prefix . "address_book AS ab
            WHERE ab.customers_id = $customer[customers_id]";

        $addresses = parent::$_adapter->fetchAssoc($addresses);

        return array(
            'customer' => $customer,
            'addresses' => $addresses
        );
    }

    private function getOrder()
    {
        $order = "SELECT o.*
            FROM " . parent::$_table_prefix . "orders AS o
            LIMIT $_SESSION[processed_count], 1";

        $order = parent::$_adapter->fetchRow($order);

        if (!$order)
            return false;

        $status_history = "SELECT osh.date_added, osh.*
            FROM " . parent::$_table_prefix . "orders_status_history AS osh
            WHERE osh.orders_id = $order[orders_id]
            ORDER BY osh.date_added DESC";

        $status_history = parent::$_adapter->fetchAssoc($status_history);

        $status_ids = array();

        foreach ($status_history as $status) {
            $status_ids[] = $status['orders_status_id'];
        }

        $status = "SELECT os.*
            FROM " . parent::$_table_prefix . "orders_status AS os
            WHERE os.orders_status_id IN (" . implode(',', $status_ids) . ')';

        $status = parent::$_adapter->fetchAll($status);

        $statuses = array();

        foreach ($status as $row) {
            $statuses[$row['orders_status_id']][$row['language_id']] = $row['orders_status_name'];
        }

        $products = "SELECT op.*
            FROM " . parent::$_table_prefix . "orders_products AS op
            WHERE op.orders_id = $order[orders_id]";

        $products = parent::$_adapter->fetchAll($products);

        $result = array();

        foreach($products as $key => $values) {

            $attributes = "SELECT opa.*
                FROM " . parent::$_table_prefix . "orders_products_attributes AS opa
                WHERE opa.orders_products_id = $values[orders_products_id]";

            $attributes = parent::$_adapter->fetchAll($attributes);

            $result[$values['orders_products_id']]['product'] = $values;
            $result[$values['orders_products_id']]['attributes'] = $attributes;

        }

        $total = "SELECT ot.*
            FROM " . parent::$_table_prefix . "orders_total AS ot
            WHERE ot.orders_id = $order[orders_id]";

        $total = parent::$_adapter->fetchAll($total);

        /*error_log(Zend_Debug::dump(array(
            'order' => $order,
            'total' => $total,
            'statuses' => $statuses,
            'status_history' => $status_history,
            'products' => $result
        )));*/

        return array(
            'order' => $order,
            'total' => $total,
            'statuses' => $statuses,
            'status_history' => $status_history,
            'products' => $result
        );
    }

    private function isImage($var)
    {
        return false === strpos('products_image', $var) ? false : true;
    }
}