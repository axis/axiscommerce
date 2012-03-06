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
 * @package     Axis_GoogleBase
 * @subpackage  Axis_GoogleBase_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_GoogleBase
 * @subpackage  Axis_GoogleBase_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_GoogleBase_Admin_IndexController extends Axis_Admin_Controller_Back
{
    /**
     *
     * @var Zend_Gdata_Gbase
     */
    private $_service;

    private function _getProductAttributes(&$product, $languageId)
    {
        $optionIds = array();
        $optionValueIds = array();
        $attributes = array();
        foreach ($product->findDependentRowset(
                'Axis_Catalog_Model_Product_Attribute', 'Product') as $attrRow) {

            /* collect attribute value */
            $option = $attrRow->findParentRow(
                'Axis_Catalog_Model_Product_Option', 'Option'
            );
            if ($option->isInputable() && !$attrRow->isModifier()) {
                foreach ($attrRow->findDependentRowset(
                        'Axis_Catalog_Model_Product_Attribute_Value') as $value) {

                    $attributes[$attrRow->id]['values'] = $value->attribute_value;
                }
            }
            /* if attr hasn't defined value */
            if (!$attrRow->option_value_id
                && !isset($attributes[$attrRow->id]['values']))
            {
                continue;
            }
            /* collect options to $attributes */
            $optionIds[$attrRow->option_id] = $attrRow->option_id;
            $optionValueIds[$attrRow->option_value_id] = $attrRow->option_value_id;

            $attributes[$attrRow->id]['id'] = $attrRow->id;
            $attributes[$attrRow->id]['option_id'] = $attrRow->option_id;
            $attributes[$attrRow->id]['option_value_id'] = $attrRow->option_value_id;
        }
        /* collect & fill labels */
        // option_text
        if (sizeof($optionIds)) {
            $optionText = Axis::single('catalog/product_option_text')
                ->select()
                ->where('option_id IN(?)', $optionIds)
                ->where('language_id = ?', $languageId)
                ->fetchAssoc();
        }
        // option_values
        if (sizeof($optionValueIds)) {
            $optionValueText = Axis::single('catalog/product_option_value_text')
                ->select('*')
                ->where('option_value_id IN (?)', $optionValueIds)
                ->where('language_id = ?', $languageId)
                ->fetchAssoc();
        }
        foreach ($attributes as $attrId => &$attrRow) {
            $attrRow['optionName'] = $optionText[$attrRow['option_id']]['name'];
            if ($attrRow['option_value_id']) {
                $attrRow['valueName'] =
                    $optionValueText[$attrRow['option_value_id']]['name'];
            }
        }
        return $attributes;
    }

    private function _getProductId($categoryId, $recursive)
    {
        $category = Axis::model('catalog/category')->find($categoryId)->current();

        if (!$category) {
            return false;
        }

        $select = Axis::model('catalog/product')->select('cp.id')
            ->join('catalog_product_category', 'cp.id = cpc.product_id')
            ->join('catalog_category', 'cc.id = cpc.category_id')
            ->where('cc.site_id = ?', $category->site_id)
            ->order('cp.id', 'ASC');

        if ($recursive && $category->lvl != 0) {
            $select->where("cc.lft BETWEEN $category->lft AND $category->rgt")
                ->where("cc.rgt BETWEEN $category->lft AND $category->rgt");
        } elseif ($category->lvl != 0) {
            $select->where('cc.id = ?', $category->id);
        }

        if (!empty(Axis::session('gbase')->last_exported)) {
            $select->where('cp.id > ?', Axis::session('gbase')->last_exported);
        }

        return $select->fetchOne();
    }

    private function _validateLocale($country, $language, $currency)
    {
        switch ($country){
            case 'US':
                    if ($language != 'en' || $currency != 'usd')
                return false;
            case 'DE':
                    if ($language != 'de' || $currency != 'eur')
                return false;
            case 'GB':
                    if ($language != 'en' || $currency != 'gbp')
                return false;
        }
    }

    /**
     * Retrieve site url including baseurl
     *
     * @param int $siteId
     * @return string|bool
     */
    private function _getSiteUrl($siteId)
    {
        if ($site = Axis::single('core/site')->find($siteId)->current()) {
            return $site->base;
        }
        return false;
    }

    private function _getAuthSubClient()
    {
        if (!isset($_SESSION['sessionToken']) && !isset($_GET['token'])) {
                $request = $this->getRequest();

                $nextUrl = $request->getScheme() . '://'
                    .  $request->getHttpHost() . $request->getRequestUri();
                $scope = 'http://www.google.com/base/feeds/items';
                $secure = false;
                $session = true;

                $authSubUrl = Zend_Gdata_AuthSub::getAuthSubTokenUri(
                    $nextUrl, $scope, $secure, $session
                );

                header("HTTP/1.0 307 Temporary redirect");
                header("Location: " . $authSubUrl);

                exit();
        }

        if (!isset($_SESSION['sessionToken']) && isset($_GET['token'])) {
                $_SESSION['sessionToken'] =
                    Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
        }

        $client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);

        return $client;
    }

    private function _getService()
    {
        $authType = Axis::config()->gbase->auth->connection;
        try {
            if ($authType == Axis_GoogleBase_Model_Option_ConnectionType::CLIENT_LOGIN) {
                $this->_service = Zend_Gdata_Gbase::AUTH_SERVICE_NAME;
                $user = Axis::config()->gbase->auth->login;
                $password = Axis::config()->gbase->auth->password;
                $client = Zend_Gdata_ClientLogin::getHttpClient(
                    $user, $password, $this->_service
                );
            } else {
                $client = $this->_getAuthSubClient();
            }
            $this->_service = new Zend_Gdata_Gbase($client);
        } catch (Exception $e) {
            Axis::message()->addError($this->view->escape($e->getMessage()));
            try {
                $log = new Zend_Log(new Zend_Log_Writer_Stream(
                    Axis::config()->system->path . '/var/logs/gbase.log'
                ));
                $log->err($e->getMessage());
            } catch (Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
        }
    }

    private function _insert($productId, &$params)
    {
        $dryRun = Axis::config()->gbase->main->dryRun;

        $tableLanguage = Axis::single('locale/language');
        $currentLanguage = $tableLanguage->find($params['language'])->current();

        /* Get local product data */
        $tableProduct = Axis::single('catalog/product');
        $tableManufacture = Axis::single('catalog/product_manufacturer');

        $currencyModel = Axis::single('locale/currency');
        $zendCurrency = $currencyModel->getCurrency($params['currency']);
        $rate = $currencyModel->getData($params['currency'], 'rate');
        $currencyOptions = array(
            'position' => Zend_Currency::RIGHT,
            'display'  => Zend_Currency::USE_SHORTNAME
        );

        $product = $tableProduct->find($productId)->current();
        $productUrl = $product->getHumanUrl();
        $productDescription = $product->getDescription($params['language']);

        if ($product->manufacturer_id != '0') {
            $productManufacture = $tableManufacture->find(
                $product->manufacturer_id
            )->current();
        }

        $productCategories = $product->getCategories($params['language']);

        $entry = $this->_service->newItemEntry();

        /* Atom namespace */
        $entry->title = $this->_service->newTitle(
            trim($productDescription['name'])
        );
        $content = $productDescription['description'] == '' ?
            Axis::translate('catalog')->__(
                'No description available'
            ) : trim($productDescription['description']);
        $entry->content = $this->_service->newContent($content);
        $entry->content->type = 'text';
        $entry->itemType = Axis::config()->gbase->main->itemType;

        if (Axis_GoogleBase_Model_Option_LinkType::WEBSITE === Axis::config('gbase/main/link')
            && $siteUrl = $this->_getSiteUrl($params['site'])) {

            $link = new Zend_Gdata_App_Extension_Link();
            $link->setHref(
                preg_replace('/\s+/', '%20', trim(
                    $siteUrl .
                    '/' . $this->view->catalogUrl . '/' .
                    $productUrl
                ))
            );
            $link->setRel('alternate');
            $link->setType('text/html');
            $link->setTitle(trim($productDescription['name']));
            $linkArray[] = $link;
            $entry->setLink($linkArray);
        } else {
            $entry->addGbaseAttribute(
                'payment_accepted', 'Google Checkout', 'text'
            );
        }

        /* G namespace */
        //$price = $zendCurrency->toCurrency($product->price * $rate, $currencyOptions);
        $price = $product->price * $rate;
        $entry->addGbaseAttribute('price', $price, 'floatUnit');
        $entry->addGbaseAttribute('condition', 'new', 'text');
        $entry->addGbaseAttribute(
            'id', $product->sku . '_' . $params['country'], 'text'
        );
        $entry->addGbaseAttribute('quantity', (int)$product->quantity, 'int');
        $entry->addGbaseAttribute(
            'weight', "$product->weight lbs", 'numberUnit'
        ); //@TODO get weight unit
        if ($productManufacture) {
            $entry->addGbaseAttribute(
                'brand', $productManufacture->name, 'text'
            );
        }
        $entry->addGbaseAttribute(
            'target_country', $params['country'], 'text'
        );
        $entry->addGbaseAttribute(
            'item_language', $currentLanguage->code, 'text'
        );
        if ($siteUrl) {
            $entry->addGbaseAttribute(
                'image_link',
                preg_replace('/\s+/', '%20', trim(
                    $siteUrl .
                    '/media' . '/product' .
                    $product->image_base
                )),
                'url'
            );
        }
        foreach ($productCategories as $category) {
            $entry->addGbaseAttribute(
                'product_type', $category['name'], 'text'
            );
        }
        foreach (Axis::config()->gbase->main->payment as $payment) {
            if ($payment != '')
                $entry->addGbaseAttribute('payment', $payment, 'text');
        }
        if (Axis::config()->gbase->main->notes != '') {
            $entry->addGbaseAttribute(
                'payment_notes', Axis::config()->gbase->main->notes, 'text'
            );
        }
        if (Axis::config()->gbase->main->application != '') {
            $entry->addGbaseAttribute(
                'application', Axis::config()->gbase->main->application, 'text'
            );
        }

        $attributes = $this->_getProductAttributes(
            $product, $params['language']
        );

        foreach ($attributes as $attr_id => $attribute) {
            $attrName = $attribute['optionName'];
            $attrValue = isset($attribute['valueName']) ?
                $attribute['valueName'] : $attribute['values'];
            $entry->addGbaseAttribute(
                preg_replace('/\s+/', '_', $attrName), $attrValue, 'text'
            );
        }

        /* Private attributes */
        $entry->addGbaseAttribute('site', $params['site'], 'int');

        $array = $entry->getExtensionElements();

        $privateAttr = array();
        $privateAttr[0]['name'] = 'access';
        $privateAttr[0]['value'] = 'private';
        $privateAttr[1]['name'] = 'type';
        $privateAttr[1]['value'] = 'int';

        $localId = new Zend_Gdata_App_Extension_Element(
            'local_id', 'g', 'http://base.google.com/ns/1.0', $product->id
        );
        $localId->setExtensionAttributes($privateAttr);

        $array[count($array)] = $localId;

        $entry->setExtensionElements($array);

        Axis::session('gbase')->last_exported = $productId;
        Axis::session('gbase')->processed_count++;

        try {
            $this->_service->insertGbaseItem($entry, $dryRun);
            Axis::session('gbase')->imported_count++;
        } catch (Exception $e) {
            Axis::message()->addError($this->view->escape($e->getMessage()));
            try {
                $log = new Zend_Log(new Zend_Log_Writer_Stream(
                    Axis::config()->system->path . '/var/logs/gbase.log'
                ));
                $log->err($e->getMessage());
            } catch (Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
        }
    }

    private function _setControl($id, &$control)
    {
        $entry = $this->_service->getEntry($id);
        $entry->setControl($control);

        Axis::session('gbase')->processed_count++;

        try {
            $entry->save();
            Axis::session('gbase')->imported_count++;
        } catch (Exception $e) {
            Axis::message()->addError($this->view->escape($e->getMessage()));
            try {
                $log = new Zend_Log(new Zend_Log_Writer_Stream(
                    Axis::config()->system->path . '/var/logs/gbase.log'
                ));
                $log->err($e->getMessage());
            } catch (Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
        }
    }

    private function _delete($id)
    {
        $entry = $this->_service->getEntry($id);

        Axis::session('gbase')->processed_count++;

        try {
            $entry->delete();
            Axis::session('gbase')->imported_count++;
        } catch (Exception $e) {
            Axis::message()->addError($this->view->escape($e->getMessage()));
            try {
                $log = new Zend_Log(
                    new Zend_Log_Writer_Stream(
                        Axis::config()->system->path . '/var/logs/gbase.log'
                    )
                );
                $log->err($e->getMessage());
            } catch (Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
        }
    }

    public function init()
    {
        parent::init();
        $this->_getService();
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('Axis_GoogleBase')->__('Google Base');

        $this->view->gCountries = array(
            'US' => 'United States',
            'DE' => 'Germany',
            'GB' => 'United Kingdom'
        );

        /* Get available currencies from store */
        $currencies = Axis::single('locale/currency')->select()->fetchAll();
        $result = array();
        foreach ($currencies as $currency) {
            $result[$currency['code']] = $currency['title'];
        }

        $this->view->currencies = $result;

        $this->render();
    }

    public function loadAction()
    {
        $this->_helper->layout->disableLayout();

        $filters = $this->_getAllParams();

        /*
         * Items count query
         */
        $count_query = $this->_service->newItemQuery();
        $count_query->setContent('stats');

        $bq = '';
        if (isset($filters['country']) && $filters['country'] != '')
            $bq .= "[target country: $filters[country]]";
        if (isset($filters['site_id']) && $filters['site_id'] != '')
            $bq .= "[site: $filters[site_id]]";
        if (isset($filters['product_type']) && $filters['product_type'] != '')
            $bq .= "[product_type: $filters[product_type]]";
        if ($bq != '')
            $count_query->setBq($bq);

        $count_feed = $this->_service->getGbaseItemFeed($count_query);
        $total_count = $count_feed->getTotalResults();

        /*
         * Items content query
         */
        $query = $this->_service->newItemQuery();
        $query->setContent('attributes,meta');

        if ($bq != '')
            $query->setBq($bq);

        $query->setMaxResults(isset($filters['limit']) ?
            $filters['limit'] : 25);
        $query->setStartIndex(isset($filters['start']) ?
            $filters['start'] + 1 : 1);

        $orderBy = '';
        switch ($filters['sort']) {
            case 'price':
                switch ($filters['country']) {
                    case 'US':
                        $orderBy = 'price(float usd)';
                        break;
                    case 'DE':
                        $orderBy = 'price(float eur)';
                        break;
                    case 'GB':
                        $orderBy = 'price(float gbp)';
                        break;
                    default:
                        $orderBy = '';
                        break;
                }
                break;
            case 'modification_time':
                $orderBy = "$filters[sort]";
                break;
            default:
                $orderBy = "$filters[sort]($filters[sortType])";
                break;
        }
        if ($orderBy != '') {
            $query->setOrderBy($orderBy);
            $query->setSortOrder($filters['dir'] == 'ASC' ?
                'ascending' : 'descending');
        }

        try {
            $feed = $this->_service->getGbaseItemFeed($query);
        } catch (Exception $e) {
            Axis::message()->addError($this->view->escape($e->getMessage()));
            try {
                $log = new Zend_Log(
                    new Zend_Log_Writer_Stream(
                        Axis::config()->system->path . '/var/logs/gbase.log'
                    )
                 );
                $log->err($e->getMessage());
            } catch (Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
            exit();
        }
        $result = array();
        $i = 0;
        foreach ($feed->entries as $entry) {

            $result[$i]['title'] = $entry->title->getText();
            $result[$i]['id'] = $entry->id->getText();
            $result[$i]['modification_time'] = substr(
                $entry->updated->getText(), 0, 10
            );

            /*
             * Base attributes
             */
            $price = $entry->getGbaseAttribute('price');
            $quantity = $entry->getGbaseAttribute('quantity');
            $expiration_date = $entry->getGbaseAttribute('expiration_date');
            $language = $entry->getGbaseAttribute('item_language');
            $result[$i]['price'] = $price[0]->text;
            $result[$i]['currency'] = substr($price[0]->text, -3);
            $result[$i]['quantity'] = $quantity[0]->text;
            $result[$i]['expiration_date'] = substr(
                $expiration_date[0]->text, 0, 10
            );
            $result[$i]['language'] = $language[0]->text;

            /*
             * Private attributes
             */
            $localId = $entry->getGbaseAttribute('local_id');
            $site = $entry->getGbaseAttribute('site');
            $result[$i]['local_id'] = $localId[0]->text;
            $result[$i]['site_id'] = $site[0]->text;

            /*
             * Extension Attributes (getting clicks, imressions, page_views)
             */
            $extensionElements = $entry->getExtensionElements();

            foreach ($extensionElements as $extElement) {
                $innerElements = $extElement->getExtensionElements();
                foreach ($innerElements as $innerElement)  {
                    $elName = $innerElement->rootElement;
                    $attributes = $innerElement->getExtensionAttributes();
                    foreach ($attributes as $aName => $aValue) {
                        $result[$i][$elName] = $aValue['value'];
                    }
                }
            }

            /*
             * Control attributes (draft, dissaproved, published)
             */
            $control = $entry->getControl();
            $result[$i]['status'] = 'published';
            if (isset($control)) {
                $draftElement = $control->draft;
                $elName = $draftElement->rootElement;
                $result[$i]['status'] = $elName;

                $extensionElements = $control->getExtensionElements();
                foreach ($extensionElements as $extElement) {
                    $elName = $extElement->rootElement;
                    $result[$i]['status'] = $elName;
                }
            }

            $i++;
        }

        $this->_helper->json->sendJson(array(
            'total_count' => $total_count->getText(),
            'feed' => $result
        ));
    }

    public function setStatusAction()
    {
        $timeStartScript = time();

        $this->_helper->layout->disableLayout();

        $disabled = $this->_getParam('draft');
        $clearSession = $this->_getParam('clearSession');
        $items = Zend_Json::decode($this->_getParam('items'));

        $draft   = new Zend_Gdata_App_Extension_Draft($disabled);
        $control = new Zend_Gdata_App_Extension_Control($draft);

        if ($clearSession) {
            unset(Axis::session('gbase')->last_exported);
            Axis::session('gbase')->processed_count = 0;
            Axis::session('gbase')->imported_count = 0;
        }

        $timeExport = 0;
        $last = false;

        while(true){
            $timeStartItem = time();

            if (Axis::session('gbase')->processed_count == count($items)) {
               $last = true;
               break;
            }

            $this->_setControl(
                $items[Axis::session('gbase')->processed_count], $control
            );

            $timeEndItem = time();
            $timeSript = $timeEndItem - $timeStartScript;
            $timeExportItem = max($timeEndItem - $timeStartItem, $timeExport);
            if (($timeSript + $timeExportItem) > 20) {
                break;
            }
        }

        $messages = array();
        if ($last) {

            Axis::message()->addSuccess(
               Axis::translate('admin')->__(
                   'Status was updated successfully %d item(s) was updated. %d item(s) was skipped',
                    Axis::session('gbase')->imported_count,
                    count($items) - Axis::session('gbase')->imported_count
                )
            );
        }

        $this->_helper->json->sendSuccess(array(
            'finalize' => $last,
            'processed' => Axis::session('gbase')->imported_count,
            'count' => count($items)
        ));
    }

    public function removeAction()
    {
        $timeStartScript = time();

        $this->_helper->layout->disableLayout();

        $items = Zend_Json::decode($this->_getParam('items'));
        $clearSession = $this->_getParam('clearSession');

        if ($clearSession) {
            unset(Axis::session('gbase')->last_exported);
            Axis::session('gbase')->processed_count = 0;
            Axis::session('gbase')->imported_count = 0;
        }

        $timeExport = 0;
        $last = false;

        while(true){
            $timeStartItem = time();

            if (Axis::session('gbase')->processed_count == count($items)) {
               $last = true;
               break;
            }

            $this->_delete($items[Axis::session('gbase')->processed_count]);

            $timeEndItem = time();
            $timeSript = $timeEndItem - $timeStartScript;
            $timeExportItem = max($timeEndItem - $timeStartItem, $timeExport);
            if (($timeSript + $timeExportItem) > 20) {
                break;
            }
        }

        $messages = array();
        if ($last) {
            Axis::message()->addSuccess(
                Axis::translate('admin')->__(
                    'Data was deleted successfully. %d item(s) was deleted. %d item(s) was skipped',
                    Axis::session('gbase')->imported_count,
                    count($items) - Axis::session('gbase')->imported_count
                )
            );
        }

        $this->_helper->json->sendSuccess(array(
            'finalize' => $last,
            'processed' => Axis::session('gbase')->imported_count,
            'count' => count($items)
        ));
    }

    public function updateAction()
    {
        $time_start_script = time();

        $this->_helper->layout->disableLayout();

        $items = Zend_Json::decode($this->_getParam('items'));
        $params = $this->_getAllParams();

        $tableLanguage = Axis::single('locale/language');

        if ($params['clearSession']) {
            unset(Axis::session('gbase')->last_exported);
            Axis::session('gbase')->processed_count = 0;
            Axis::session('gbase')->imported_count = 0;
        }

        $time_export = 0;
        $last = false;

        while (true) {
            $time_start_item = time();

            if (Axis::session('gbase')->processed_count == count($items)) {
               $last = true;
               break;
            }

            $params['id'] = $items[Axis::session('gbase')->processed_count]['id'];
            $params['site'] = $items[Axis::session('gbase')->processed_count]['site'];
            $params['currency'] = $items[Axis::session('gbase')->processed_count]['currency'];
            $params['language'] = $tableLanguage
                ->getIdByCode($items[Axis::session('gbase')->processed_count]['language']);
            $params['language_code'] = $items[Axis::session('gbase')->processed_count]['language'];

            $this->_update(
                $items[Axis::session('gbase')->processed_count]['local_id'], $params
            );

            $time_end_item = time();
            $time_sript = $time_end_item - $time_start_script;
            $time_export_item = max(
                $time_end_item - $time_start_item, $time_export
            );
            if (($time_sript + $time_export_item) > 20) {
                break;
            }
        }

        $messages = array();
        if ($last) {
            Axis::message()->addSuccess(
                Axis::translate('admin')->__(
                    'Data was updated successfully. %d item(s) was updated. %d item(s) was skipped',
                    Axis::session('gbase')->imported_count,
                    count($items) - Axis::session('gbase')->imported_count
                )
            );
        }

        $this->_helper->json->sendSuccess(array(
            'finalize' => $last,
            'processed' => Axis::session('gbase')->imported_count,
            'count' => count($items)
        ));
    }

    public function exportAction()
    {
        $time_start_script = time();

        $this->_helper->layout->disableLayout();

        $items = Zend_Json::decode($this->_getParam('items'));
        $params = $this->_getAllParams();

        if ($params['clearSession']) {
            unset(Axis::session('gbase')->last_exported);
            Axis::session('gbase')->processed_count = 0;
            Axis::session('gbase')->imported_count = 0;
        }

        $time_export = 0;
        $last = false;

        while (true) {
            $time_start_item = time();

            if (Axis::session('gbase')->processed_count == count($items)) {
               $last = true;
               break;
            }

            $this->_insert($items[Axis::session('gbase')->processed_count], $params);

            $time_end_item = time();
            $time_sript = $time_end_item - $time_start_script;
            $time_export_item = max(
                $time_end_item - $time_start_item, $time_export
            );
            if (($time_sript + $time_export_item) > 20) {
                break;
            }
        }

        $messages = array();
        if ($last) {
            Axis::message()->addSuccess(
                Axis::translate('admin')->__(
                    'Export process has been completed. %d item(s) was exported. %d item(s) was skipped',
                    Axis::session('gbase')->imported_count,
                    count($items) - Axis::session('gbase')->imported_count
                )
            );
        }

        $this->_helper->json->sendSuccess(array(
            'finalize' => $last,
            'processed' => Axis::session('gbase')->imported_count,
            'count' => count($items)
        ));
    }

    public function exportBranchAction()
    {
        $time_start_script = time();

        $this->_helper->layout->disableLayout();

        $params = $this->_getAllParams();

        if ($params['clearSession']) {
            unset(Axis::session('gbase')->last_exported);
            Axis::session('gbase')->processed_count = 0;
            Axis::session('gbase')->imported_count = 0;
        }

        $productModel  = Axis::model('catalog/product');
        $categoryModel = Axis::model('catalog/category');
        $category = $categoryModel->find($params['catId'])->current();

        if ($category->lvl == 0 || $params['recursive']) {
            $count = $category->getProductsCount(true);
        } else {
            $count = $category->getProductsCount();
        }

        $time_export = 0;
        $last = false;

        while (true) {
            $time_start_item = time();
            $product_id = $this->_getProductId(
                $params['catId'], $params['recursive']
            );
            if (!$product_id) {
               $last = true;
               break;
            }
            $this->_insert($product_id, $params);
            $time_end_item = time();
            $time_sript = $time_end_item - $time_start_script;

            $time_export_item = max(
                $time_end_item - $time_start_item, $time_export
            );

            if (($time_sript + $time_export_item) > 20) {
                break;
            }
        }

        $messages = array();
        if ($last) {
            Axis::message()->addSuccess(
                Axis::translate('admin')->__(
                    'Export process has been completed. %d item(s) was exported. %d item(s) was skipped',
                    Axis::session('gbase')->imported_count,
                    $count - Axis::session('gbase')->imported_count
                )
            );
        }

        $this->_helper->json->sendSuccess(array(
            'finalize' => $last,
            'processed' => Axis::session('gbase')->imported_count,
            'count' => $count
        ));
    }

    public function revokeTokenAction()
    {
        $this->_helper->layout->disableLayout();

        Zend_Gdata_AuthSub::AuthSubRevokeToken($_SESSION['sessionToken']);

        unset($_SESSION['sessionToken']);
    }
}