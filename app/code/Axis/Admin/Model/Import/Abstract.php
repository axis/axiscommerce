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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Admin_Model_Import_Abstract implements Axis_Admin_Model_Import_Interface
{
    const HUMAN_URL = 'catalog_hurl';
    const CATEGORY = 'catalog_category';
    const CATEGORY_DESCRIPTION = 'catalog_category_description';
    const MANUFACTURER = 'catalog_product_manufacturer';
    const PRODUCT_OPTION = 'catalog_product_option';
    const PRODUCT_OPTION_TEXT = 'catalog_product_option_text';
    const PRODUCT_OPTION_VALUE = 'catalog_product_option_value';
    const PRODUCT_OPTION_VALUE_TEXT = 'catalog_product_option_value_text';
    const PRODUCT_OPTION_VALUESET = 'catalog_product_option_valueset';
    const PRODUCT = 'catalog_product';
    const PRODUCT_STOCK = 'catalog_product_stock';
    const PRODUCT_GALLERY = 'catalog_product_gallery';
    const PRODUCT_DESCRIPTION = 'catalog_product_description';
    const PRODUCT_ATTRIBUTE = 'catalog_product_attribute';
    const PRODUCT_ATTRIBUTE_VALUE = 'catalog_product_attribute_value';
    const PRODUCT_TO_CATEGORIES = 'catalog_product_category';
    const CUSTOMER = 'account_customer';
    const CUSTOMER_ADDRESS = 'account_customer_address';
    const ORDER_STATUS = 'sales_order_status';
    const ORDERS = 'sales_order';
    const ORDER_PRODUCT = 'sales_order_product';
    const ORDER_PRODUCT_ATTRIBUTE = 'sales_order_product_attribute';
    const ORDER_TOTAL = 'sales_order_total';

    protected static $__CLASS__ = __CLASS__;
    protected static $_adapter = null;
    protected static $_instance = null;
    protected static $_image_path = null;
    protected static $_table_prefix = null;
    protected $_language = null;
    protected $_primary_language = null;
    protected $_site = null;
    protected $_root = null;
    protected static $_db_prefix = '';

    public static function getInstance($data)
    {
        $class = self::_getClass(); //get_called_class() - available in php 5.3

        if (null === self::$_instance) {
            try {
                self::$_table_prefix = $data['table_prefix'];
                self::$_image_path = $data['image_path'];
                self::$_instance = new $class();
                self::$_adapter = Zend_Db::factory('pdo_mysql', array(
                    'host'     => $data['host'],
                    'username' => $data['db_user'],
                    'password' => $data['db_password'],
                    'dbname'   => $data['db_name'],
                    'driver_options' => array(
                        //PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
                        1002 => 'SET NAMES UTF8'
                    )
                ));
                self::$_adapter->getConnection();
                self::$_db_prefix = Axis::config()->db->prefix;
            } catch (Exception $e) {
                error_log($e->getMessage());
                return false;
            }
        }

        return self::$_instance;
    }

    public function dispose()
    {
        self::$_instance = null;

        if (null === self::$_adapter)
            return;

        self::$_adapter->closeConnection();
        self::$_adapter = null;
    }

    /**
     * Returns the classname of the child class extending this class
     *
     * @static
     * @return string The class name
     */
    private static function _getClass()
    {
        $implementing_class = self::$__CLASS__;
        $original_class = __CLASS__;

        if ($implementing_class === $original_class)
            throw new Axis_Exception("You MUST provide a <code>protected static \$__CLASS__ = __CLASS__;</code> statement in your Singleton-class!");

        return $implementing_class;
    }

    protected function _prepareString($string, $replacement = '-', $camel = false)
    {
        if ($camel) {
            $parts = explode(' ', $string);
            $result = "";
            foreach ($parts as $part)
                $result .= ucfirst($part);

            return substr($result, 0, 40);
        } else {
            return str_replace(array(' ', '/', '\\'), $replacement, strtolower(substr($string, 0, 40)));
        }
    }

    protected function _copyFile($from, $to)
    {
        $path = explode('/', $from);

        $file = $path[count($path)-1];

        $file = str_replace(' ', '_', $file);

        $imagesPath = $savePath = $to;

        for ($i = 0; $i < 2; $i++) {
            if (!preg_match('/[a-zA-Z0-9]{1}/', $file[$i])) {
                break;
            }

            $savePath .= '/' . strtolower($file[$i]);
            if (!file_exists($savePath)) {
                @mkdir($savePath);
            }
        }
        $savePath .= '/' . $file;
        @copy($from, $savePath);
        return substr($savePath, strlen($imagesPath));
    }
}