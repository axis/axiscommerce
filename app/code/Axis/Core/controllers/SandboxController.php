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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class SandboxController extends Axis_Core_Controller_Front
{
    public function indexAction()
    {   
        

        Axis::single('core/config_field')
            
            ->add('account/address_form/company_value', 'Company Default Value')
            ->add('account/address_form/phone_value', 'Phone Default Value')
            ->add('account/address_form/fax_value', 'Fax Default Value')
            ->add('account/address_form/street_address_value', 'Street Address Default Value')
            ->add('account/address_form/city_value', 'City Default Value')
            ->add('account/address_form/zone_id_value', 'State(Region) Default Value', 12, 'text', 'You can get the id of desired region at [admin]/location/zone')
            ->add('account/address_form/postcode_value', 'Postcode Default Value', 90064)
            ->add('account/address_form/country_id_value', 'Country Default Value', 223, 'select', array('model' => 'location/option_country'))
            
                        
            ->transform()
;
         
Axis::single('core/config_builder')
//    ->remove('core3')
//        ->remove('mail3')
//    ->remove('design3')
//    ->section('/')
    ;
        
        Zend_Debug::dump(Axis_Payment::getMethodNames());
        Zend_Debug::dump(Axis::config('payment/CreditCard_Standard/shippings'));
//        Zend_Debug::dump(Axis::config('account/address_form/country_id_allow')->toArray());
        
        
//        die;
        Zend_Debug::dump(Axis::config('shipping/Flat_Standard/multiPrice'));
        Zend_Debug::dump(Axis::config('shipping/Flat_Standard/multiPrice')->toArray());
        
        
//        $routeAdmin = new Axis_Controller_Router_Route_Admin(
//            'admin/:controller/:action/*',
//            array(
//                'module' => 'Axis_Admin',
//                'controller' => 'index',
//                'action' => 'index'
//            )
//        );
//        $routeAccount = new Axis_Controller_Router_Route_Admin(
//            'admin/account/:controller/:action/*',
//            array(
//                'module' => 'Axis_Account',
//                'controller' => 'index',
//                'action' => 'index'
//            )
//        );
//        
//        $urls = array(
//            'admin',
//            'admin/',
//            'admin/account',
//            'admin/account/',
//            'admin/account/index/view/id/3',
//            'admin/account/customer/view/id/3'
//        );
//        foreach ($urls as $url) {
//            Zend_Debug::dump($routeAdmin->match($url), $url );
//            Zend_Debug::dump($routeAccount->match($url), $url );
//            echo '--------------------------';
//        }
        
        $row = Axis::model('account/customer')->find(1)->current();
        $row->password  = 1; 
        $_row = array();
        Zend_Debug::dump(!isset($_row['name']));
        Zend_Debug::dump(empty($_row['name']));
        $_row['name'] = '';
        Zend_Debug::dump(empty($_row['name']));
        $_row['name'] = null;
        Zend_Debug::dump(empty($_row['name']));
        $_row['name'] = false;
        Zend_Debug::dump(empty($_row['name']));
        $_row['name'] = 'xxx';
        Zend_Debug::dump(empty($_row['name']));
        
        $select = Axis::model('cms/page_content')->select('*')
                ->join('cms_page_category', 'cpc2.cms_page_id = cpc.cms_page_id')
                ->join('cms_category', 
                    'cc.id = cpc2.cms_category_id',
                    'site_id'
                )->joinLeft('locale_language', 
                    'll.id = cpc.language_id',
                    'locale'
                )
                ;
        $rowset = $select->fetchRowset();
//        $s = Axis::model('search/indexer')->make()
            ;
            
        Zend_Debug::dump($rowset->toArray());
        
//        Zend_Debug::dump();
        $this->view->meta()->setTitle('片　视 频　地');
        Axis_FirePhp::timeStamp('333');
//        $o = new Axis_Object();
//        $o->sub = new Axis_Object(array('a' => 1));
//        Zend_Debug::dump($o->sub->a);
//        $o->sub->a = 2;
//        Zend_Debug::dump($o->sub->a);
//        die;
        //var_dump( Axis::config()->cache->main->lifetime->toArray());
        //Axis_FirePhp::log(Axis::config()->cache->main->lifetime->toArray());
        //======================================================================
        /*$email = '0m3r.mail@gmail.com';
        //$expr = new Zend_Db_Expr('NOW()');
        $expr = Axis_Date::now()->toSQLString();
        $select = Axis::model('admin/user')->getAdapter()->select();
        $select->from('admin_user')->where("created < ? ", $expr);
        $res = $select->query()->fetchAll();*/

        //Axis::cache()->clean();
        //Axis::cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('err'));
        //======================================================================
        //$abs = new Axis_Poll_Box_Poll();

        //$abs->setCC();
        /*$code = Axis::single('PaymentCreditCard/Standard')->getCode();
         *
         */
//        $multi = array(
//            'subcode' => array('title' => 'xxx title', 'price' => '12.00'),
//            'subcode2' => array('title' => 'xxx title1', 'price' => '20.00'),
//            'subcode3' => array('title' => 'xxx title3', 'price' => '50.00')
//        );
        Axis_FirePhp::timeStamp('++++++++++++++++++++++++++++++++++++++++++++');

//        Axis_FirePhp::log("SELECT `cp`.*, `cpc`.`link`, group_concat(`cc`.`name` separator ', ') AS `category_name` FROM `prefix_cms_page` AS `cp` LEFT JOIN `prefix_cms_page_content` AS `cpc` ON cp.id = cpc.cms_page_id AND cpc.language_id = 1 LEFT JOIN `prefix_cms_page_category` AS `cptc` ON cptc.cms_page_id = cp.id LEFT JOIN `prefix_cms_category` AS `cc` ON cc.id = cptc.cms_category_id GROUP BY `cp`.`id`");
        
//        $o = new Axis_Object() ;
//
//        $o->value1->a = 1;
//        $o->value2['a'] = 2;
//        $o->value2['a'] = 3;
//        $o['value3']['a'] = 2;
//        $o['value4']->a = 2;
//        $o['value5']->setA(2);
//
//        Zend_Debug::dump($o);
//        die;
        
        $this->_prefix = '';
        $where = $where2 = ':where';
        $query = "
            SELECT po.id as option_id, pot.name as option_name, pov.id as value_id, povt.name as value_name
            FROM " . $this->_prefix . 'catalog_product_option' . " po
            INNER JOIN " . $this->_prefix . "catalog_product_option_text pot ON pot.option_id = po.id
            INNER JOIN " . $this->_prefix . "catalog_product_option_value pov ON pov.valueset_id = po.valueset_id
            INNER JOIN " . $this->_prefix . "catalog_product_option_value_text povt ON povt.option_value_id = pov.id
            WHERE pot.language_id = :langId AND pot.name IN($where) AND
                  povt.language_id = :langId AND povt.name IN($where2)
        ";
        Zend_Debug::dump($query);

        $selectStr = new Axis_Db_Table_Select_Disassemble($query);

        $string = $selectStr->__toString();


        Zend_Debug::dump($string);
//        Zend_Debug::dump(Axis::single('location/country')
//                    ->getIdByName('Ukraine') . Axis::single('location/zone')->getIdByCode(
//                'CA'
//            ));
//        Zend_Debug::dump(
//            Axis::single('checkout/checkout')->getCart()->getProducts()
//        );

//        $select = Axis::single('checkout/cart_product')
//            ->select(array('id', 'quantity'))
//            ->joinLeft(
//                'checkout_cart_product_attribute',
//                'ccpa.shopping_cart_product_id = ccp.id',
//                array('attributeId' => 'product_attribute_id',
//                    'product_attribute_value'
//                )
//            )
//            ->where('ccp.shopping_cart_id = ?', 1)
//            ->where('ccp.product_id = ?', 1)
//
//            ->firephp()
//            ;
//       $myselect = Axis::single('catalog/product')
//                ->select('*')
////                ->setIntegrityCheck()
//                ->join('catalog_product_description', 'cpd.product_id = cp.id', '*')
//                ->where('cp.id = ?', 3)
//                ->where('cpd.language_id = ?', 1)
////                ->firephp()
//                ;
//        $row = Axis::single('catalog/product')->fetchRow($myselect);
//        Zend_Debug::dump($row);
            
//        Zend_Debug::dump($a);
//        Axis::single('discount/discount')->setSpecialPrice(
//            1, 99, time(), time() + 60*60*60
//        );
        // Axis::single('core/config_field')
        if (false === function_exists('camelize')) {
            function camelize($str) {
                $str = ltrim(str_replace(" ", "", ucwords(str_replace("_", " ", $str))));
                return (string)(strtolower(substr($str, 0, 1)) . substr($str, 1));
            }
        }

        if (false === function_exists('underscore')) {
            function underscore($str = null) {
                return strtolower(preg_replace(array('/(.)([A-Z])/', '/(.)(\d+)/'), "$1_$2", $str));
            }
        }
//        Zend_Debug::dump(
//            camelize('shipping_tax')
//
//            );

    //        $matches = array();
//        $str = '#10 /usr/share/php/libzend-framework-php/Zend/Controller/Dispatcher/Standard.php(289): Zend_Controller_Action->dispatch(\'indexAction\')
//#11 /usr/share/php/libzend-framework-php/Zend/Controller/Front.php(954):';
//        $str = preg_replace(
//            '/(#\d+\s)(\/.*\/[^\/]+(?:\.php|\.phtml))/',
//            "<a href=\"$2\">$1$2<\/a>",
//            $str
//        );

//        array(
//            array('a', 'A' , 1),
//            array('a', 'A' , 2),
//            array('a', 'B' , 1),
//            array('a', 'B' , 2),
//            array('a', 'C' , 1),
//            array('a', 'C' , 2),
//            array('b', 'A' , 1),
//            array('b', 'A' , 2),
//            array('b', 'B' , 1),
//            array('b', 'B' , 2),
//            array('b', 'C' , 1),
//            array('b', 'C' , 2)
//        );
//
//         $arrays = array(
//            array('a', 'b'),
//            array('A', 'B' , 'C'),
//            array(1, 2)
//        );
//
//        $results = array(array());
//        $iter = 0;
//        foreach ($arrays as $array) {
//            $temp = array();
//            foreach ($array as $item) {
//                foreach ($results as $result) {
//                    $temp[] = array_merge($result, array($item));
//                    $iter++;
//                }
//            }
//            $results = $temp;
//        }
//
//        Zend_Debug::dump(
//            $results, $iter
//        );



  $order = Axis::single('sales/order')->find(27)->current();
//        Zend_Debug::dump($order->getDelivery()->toArray());
//
        $address = Axis::single('account/customer_address')
            ->getAddress(5);
//        $address['country_id'] = $address['country']['id'];
//        $address = Axis::single('account/customer_address')
//            ->getAddress($address);

//        Zend_Debug::dump($address->toArray());
//        Zend_Debug::dump($address->toFlatArray());
//    Zend_Debug::dump(Axis::single('checkout/checkout')->getCart()->getProducts());

        Axis_FirePhp::timeStamp('Sandbox1');
        //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//        $ret = $this->db->query('show tables');
//        while (($row = $ret->fetch()))
//            $rows[] = current($row);
//        Zend_Debug::dump($rows[7]);
//        Zend_Debug::dump($rows);
//        $this->render();
        /*$path = '/var/www/demo.axiscommerce.com/public_html/axis/app/locale/front/en_US/contact-us.php';
        require($path);
        //$langData = array('rteu' => 'wewe', 'wewe' => 'wwew');
        if (is_file($path)) {
            $content = '<?php' . "\n" . ' $langData = array(' . "\t";
            foreach ($langData as $key => $value) {
                $content .= "\n\t'$key'\t=>\t'$value',";
            }
            $content = substr($content, 0, -1);
            $content .= "\n" . ');' . "\n" . '?>';
            if (!@file_put_contents($path, $content)) {
               echo 'false';
            }
        } */
        Axis_FirePhp::timeStamp('end controller render');
    }
}