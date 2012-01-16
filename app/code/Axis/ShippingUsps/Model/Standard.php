<?php
/**
 * Axis
 *
 * @copyright Portions Copyright 2003 osCommerce
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
 * @package     Axis_ShippingUsps
 * @subpackage  Axis_ShippingUsps_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingUsps
 * @subpackage  Axis_ShippingUsps_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingUsps_Model_Standard extends Axis_Method_Shipping_Model_Abstract
{
    /**
     * Declare shipping module alias code
     *
     * @var string
     */
    protected $_code = 'Usps_Standard';

    /**
     * Shipping module display name
     *
     * @var string
     */
    protected $_title = 'USPS';

    /**
     * Shipping module display description
     *
     * @var string
     */
    protected $_description;

    /**
     * Shipping module icon filename/path
     *
     * @var string
     */
    protected $_icon = 'shipping_usps.gif';

    protected $_defaultGatewayUrl = 'http://production.shippingapis.com/ShippingAPI.dll';

    protected $_testMode = false;


    // @see testing example http://www.varnagiris.net/2006/05/04/php-usps-rates-calculator/
    // @see http://kb.veracart.com/questions/127/USPS-Error-message:-%22%2880040b1a%29-Authorization-failure.-You-are-not-authorized-to-connect-to-this-server%22
    // @see http://www.usps.com/webtools/htm/Rate-Calculators-v2-1.htm#_Toc196127989
    // @see http://www.usps.com/webtools/htm/Development-Guide-v3-0b.htm#_Toc205879416

    /**
     * Get quote from shipping provider's API:
     *
     * @param string $method
     * @return array of quotation results
     */
    public function getAllowedTypes($request)
    {
        $this->_setRequest($request);
        return $this->_getQuotes($request);
    }

    protected function _setRequest($request)
    {
        $this->_request = new Axis_Object();
        $services = $this->_config->service->toArray();
        $services = (!count($services) || count($services) > 6)
            ? implode(',', $services) : 'ALL';

        $this->_request->setService($services);// test ALL

        $this->_request->setUserId($this->_config->userId);
        $this->_request->setContainer($this->_config->container);//  'VARIABLE'
        $this->_request->setSize($this->_config->size); //test Large

        $this->_request->setOriginPostal(Axis::config()->core->store->zip);

        $this->_request->setDestCountryId($request['country']['iso_code_2']);

        if ($request['country']['iso_code_2'] === 'GB') {
            $countryName = 'Great Britain and Northern Ireland';
        } else {
            $countryName = Axis::single('location/country')
                ->getNameByIsoCode2($request['country']['iso_code_2']);
        }
        $this->_request->setDestCountryName($countryName);
        $this->_request->setDestPostal(str_replace(' ', '', $request['postcode']));

        $weight = $request['weight'] < 0.1 ? 0.1 : $request['weight'];
        //test
        $this->_request->setWeightPounds(floor($weight)); //test 10
        $this->_request->setWeightOunces(round(($weight - floor($weight)) * 16, 1));//test 5

        // weight must be less than 35lbs and greater than 6 ounces or it is not machinable
        switch (true) {
            case ($this->_request->getWeightPounds() == 0 && $this->_request->getWeightOunces() < 6):
                // override admin choice too light
                $isMachinable = 'False';
                break;

            case ($weight > 35):
                // override admin choice too heavy
                $isMachinable = 'False';
                break;

            default:
                // admin choice on what to use
                $isMachinable = $this->_config->machinable ? 'True' : 'False';
        }
        $this->_request->setMachinable($isMachinable);//True

        $this->_request->setValue(
            Axis::single('locale/currency')->to($request['price'],'USD')
        );
        $this->_request->setValueWithDiscount(
            //@todo get discount from payment checkout request
            Axis::single('locale/currency')->to($request['price'], 'USD')
        );

        return $this->_request;
    }

    /**
     * Get actual quote from USPS
     *
     * @return array of results or boolean false if no results
     */
    protected function _getQuotes()
    {
        return $this->_getXmlQuotes();
    }

    protected function _getXmlQuotes()
    {
        $r = $this->_request;

        if ($this->_testMode) {
            $xml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><RateV2Request/>');

            $xml->addAttribute('USERID', $r->getUserId());

            $package = $xml->addChild('Package');

            $package->addAttribute('ID', 0);
            $package->addChild('Service', 'All');
            // or
            // $package->addChild('Service', 'Priority');
            $package->addChild('ZipOrigination', '10022');
            $package->addChild('ZipDestination', '20008');
            $package->addChild('Pounds', '10');
            $package->addChild('Ounces', '5');
            $package->addChild('Container', 'Flat Rate Box');
            $package->addChild('Size', 'Large');
            // or
            // $package->addChild('Size', 'Regular');
            $package->addChild('Machinable', 'True'); // or comment this line for the second example

            $api = 'RateV2';
        } elseif ($r->getDestCountryId() == 'US' || $r->getDestCountryId() == 'PR') {
            $xml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><RateV3Request/>');

            $xml->addAttribute('USERID', $r->getUserId());

            $package = $xml->addChild('Package');

            $package->addAttribute('ID', 0);
            $package->addChild('Service', $r->getService());

            // no matter Letter, Flat or Parcel, use Parcel
            if ($r->getService() == 'FIRST CLASS') {
                $package->addChild('FirstClassMailType', 'PARCEL');
            }
            $package->addChild('ZipOrigination', $r->getOriginPostal());
            //only 5 chars avaialble
            $package->addChild('ZipDestination', substr($r->getDestPostal(), 0, 5));
            $package->addChild('Pounds', $r->getWeightPounds());
            $package->addChild('Ounces', $r->getWeightOunces());

            $package->addChild('Container', $r->getContainer());
            $package->addChild('Size', $r->getSize());
            $package->addChild('Machinable', $r->getMachinable());

            $api = 'RateV3';

        } else {
            $xml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><IntlRateRequest/>');

            $xml->addAttribute('USERID', $r->getUserId());

            $package = $xml->addChild('Package');
            $package->addAttribute('ID', 0);
            $package->addChild('Pounds', $r->getWeightPounds());
            $package->addChild('Ounces', $r->getWeightOunces());
            $package->addChild('MailType', 'Package');
            $package->addChild('ValueOfContents', $r->getValue());
            $package->addChild('Country', $r->getDestCountryName());

            $api = 'IntlRate';
        }
        $request = $xml->asXML();
        try {
            $url = $this->_config->gateway;
            if (empty($url)) {
                $url = $this->_defaultGatewayUrl;
            }
            $httpClient = new Zend_Http_Client();
            $httpClient->setUri($this->_testMode ? 'http://testing.shippingapis.com/ShippingAPITest.dll' : $url);
            $httpClient->setHeaders(array(
            //'Host'       => $usps_server,
            'User-Agent' => 'Axis',
            'Connection' => 'Close'));
            $httpClient->setConfig(array('maxredirects' => 0, 'timeout' => 30));
            $httpClient->setParameterGet('API', $api);
            $httpClient->setParameterGet('XML', $request);
            $response =  $httpClient->request()->getBody();
        } catch (Exception $e) {
            $response = '';
        }
        return $this->_parseXmlResponse($response);
    }

    protected function _parseXmlResponse($response)
    {
        $methods = array();
        if (strlen(trim($response)) == 0) {
            $this->log('Unable to retrieve quotes');
            return $methods;
        }
        if (strpos(trim($response), '<?xml') != 0) {
            $this->log('Response is in the wrong format');
            return $methods;
        }

        if (preg_match('#<\?xml version="1.0"\?>#', $response)) {
            $response = str_replace(
                '<?xml version="1.0"?>',
                '<?xml version="1.0" encoding="ISO-8859-1"?>',
                $response
            );
        }

        $xml = simplexml_load_string($response);
        if (!is_object($xml)) {
            $this->log('Can not convert Response to Xml');
            return $methods;
        }

        if (is_object($xml->Number) && is_object($xml->Description) && (string)$xml->Description != '') {
            $this->log((string)$xml->Description);
            return $methods;
        }
        if (is_object($xml->Package) && is_object($xml->Package->Error)
            && is_object($xml->Package->Error->Description)
            && (string)$xml->Package->Error->Description != '') {

            $this->log((string)$xml->Package->Error->Description);
            return $methods;
        }

        if (!is_object($xml->Package)) {
            $this->log('I can not get access to a resource "Package"');
            return $methods;
        }

        $allowedMethods = $this->_config->allowedMethods->toArray();
        $allMethods = Axis::model('core/config_field')->select('config_options')
            ->where('path = "shipping/Usps_Standard/allowedMethods"')
            ->fetchOne();
        $allMethods = explode(',', $allMethods);

        if ($this->_request->getDestCountryId() == 'US'
            || $this->_request->getDestCountryId() == 'PR') {

            if(!is_object($xml->Package->Postage)) {
               $this->log('I can not get access to a resource "Postage"');
               return $methods;
            }
            foreach ($xml->Package->Postage as $postage) {
                if (in_array((string)$postage->MailService, $allMethods)
                    && !in_array((string)$postage->MailService, $allowedMethods)) {

                    continue;
                }
                $cost = Axis::single('locale/currency')->from(
                    (float)$postage->Rate, 'USD'
                );
                $methods[] = array(
                    'id' => $this->_code . '_' . str_replace(' ', '_', (string)$postage->MailService),
                    'title' => $this->getTranslator()->__((string)$postage->MailService),
                    'price' => $cost + $this->_config->handling
                );

            }
            return $methods;

        }

        if(is_object($xml->Package->Service)) {
           $this->log('I can not get access to a resource "Service"');
           return $methods;
        }
        foreach ($xml->Package->Service as $service) {
            if (in_array((string)$service->SvcDescription, $allMethods)
                && !in_array((string)$service->SvcDescription, $allowedMethods)) {

                continue;
            }
            $cost = Axis::single('locale/currency')->from((float)$postage->Rate, 'USD');
            $methods[] = array(
                'id' => $this->_code . '_' . str_replace(' ', '_', (string)$service->SvcDescription),
                'title' => $this->getTranslator()->__((string)$service->SvcDescription),
                'price' => $cost + $this->_config->handling
            );

        }

        return $methods;
    }
}
