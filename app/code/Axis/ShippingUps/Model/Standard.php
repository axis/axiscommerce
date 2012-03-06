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
 * @category    Axis
 * @package     Axis_ShippingUps
 * @subpackage  Axis_ShippingUps_Model
 * @copyright   Copyright 2008-2011 Axis
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @license     GNU Public License V3.0
 */

/**
 * UPS Shipping Module class
 *
 * @category    Axis
 * @package     Axis_ShippingUps
 * @subpackage  Axis_ShippingUps_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingUps_Model_Standard extends Axis_Method_Shipping_Model_Abstract
{
    /**
     * Declare shipping module alias code
     *
     * @var string
     */
    protected $_code = 'Ups_Standard';

    /**
     * Shipping module display name
     *
     * @var string
     */
    protected $_title = 'Ups';

    /**
     * Default Cgi Gateway Url
     * @var string
     */
    protected $_defaultGatewayUrl = 'http://www.ups.com/using/services/rave/qcostcgi.cgi';

     //@todo collection serrvices value|code|label 01|1da|UPS Next Day Air®
    /**
     * Subcodes
     * @var array
     */
    private $_valueToCode = array(
       '01' => '1DA', //UPS Next Day Air®
       '02' => '2DA', //UPS Second Day Air®
       '03' => 'GND', //UPS Ground
       '07' => 'XPR', //UPS Worldwide ExpressSM
       '08' => 'XPD', //UPS Worldwide ExpeditedSM
       '11' => 'STD', //UPS Standard
       '12' => '3DS', //UPS Three-Day Select®
       '13' => '1DP', //UPS Next Day Air Saver®
       '14' => '1DM', //UPS Next Day Air® Early A.M. SM
       '54' => 'XDM', //UPS Worldwide Express PlusSM
       '59' => '2DM', //UPS Second Day Air A.M.®
       '65' => 'WXS', //UPS Saver
    );
    
    /**
     * Subcodes
     * @var array
     */
    private $_codeToValue = array(
       '1DM'    => '14',
       '1DML'   => '14',
       '1DA'    => '01',
       '1DAL'   => '01',
       //'1DAPI'  => '01',
       '1DP'    => '13',
       //'1DPL'   => '13',
       '2DM'    => '59',
       '2DML'   => '59',
       '2DA'    => '02',
       '2DAL'   => '02',
       '3DS'    => '12',
       'GND'    => '03',
       'GNDCOM' => '03',
       'GNDRES' => '03',
       'STD'    => '11',
       'XPR'    => '07',
       'WXS'    => '65',
       'XPRL'   => '07',
       'XDM'    => '54',
       'XDML'   => '54',
       'XPD'    => '08'

    );

    //http://www.google.com.ua/url?sa=t&source=web&ct=res&cd=1&url=http%3A%2F%2Faricmackey.com%2Fwp-content%2Fuploads%2F2008%2F04%2Fups-servicecodes.pdf&ei=ilFwSrCgApPmnAOSnuG4Bw&usg=AFQjCNEsnvbbKqFJpNC11Usd9T7sceF1Cg&sig2=C4YzeB2s4p-xbSENCFTtRw

    /**
     * Get quote from shipping provider's API:
     *
     * @param string $method
     * @return array of quotation results
     */
    public function getAllowedTypes($request)
    {
        $this->_setRequest($request);
        $this->_types = $this->_getQuotes();
        return $this->_types;
    }


    /**
     * Set request params
     * @param array $request
     */
    protected function _setRequest($request)
    {
        $r = new Axis_Object();
        // Set UPS Product Code
        // Set UPS Action method
        switch ($this->_config->res) {
            case Axis_ShippingUps_Model_Option_Standard_DestinationType::RES: 
                $r->productCode = 'GNDRES';
                break;
            case Axis_ShippingUps_Model_Option_Standard_DestinationType::COM: 
                $r->productCode = 'GNDCOM';
                break;
        }
        $r->actionCode = '4';
         /* 3 - Single Quote (Rate)
            4 - All Available Quotes (Shop)*/
        if ('CA' === $request['country']['iso_code_2']) {
            $r->productCode = 'STD';
            $r->actionCode = '3';
        }

        // Set UPS Origin detail
        $r->originPostalCode = Axis::config()->core->store->zip;

        $r->originCountryCode = Axis::single('location/country')->find(
            Axis::config()->core->store->country
        )->current()->iso_code_2;

        $r->originZone = Axis::single('location/zone')->getCode(
            Axis::config()->core->store->zone
        );

        $r->originCity = str_replace(
            ' ', '+', ltrim(Axis::config()->core->store->city)
        );

        // Set UPS Destination information

        if ($request['country']['iso_code_2'] == 'US') {
            $r->destPostalCode = substr(
                str_replace(' ', '', $request['postcode']), 0, 5
            );
        } else {
            $r->destPostalCode = substr(
                str_replace(' ', '', $request['postcode']), 0, 6
            );
        }
        $r->destCountryCode = $request['country']['iso_code_2'];
        $r->destZone = isset($request['zone']['id']) ?
            Axis::single('location/zone')->getCode($request['zone']['id']) : '';


        // Set UPS rate-quote method
        $r->pickupCode = $this->_config->pickup;
        $r->pickupLabel =  Axis_ShippingUps_Model_Option_Standard_Pickup::getConfigOptionValue($r->pickupCode);

        // Set UPS Container type
        $r->containerCode = $this->_config->package;

        // Set UPS package weight
        $r->packageWeight = $request['weight'] < 0.1 ? 0.1 : $request['weight'];

        $r->weightUnit = $this->_config->measure;

        // @todo
        //$r->numberBoxes = $request['boxes'];

        //Set UPS address-quote method (residential vs commercial)
        switch ($this->_config->res) {
            case Axis_ShippingUps_Model_Option_Standard_DestinationType::RES: // Residential Address
                $r->residentialCode = '1';
                break;
            case Axis_ShippingUps_Model_Option_Standard_DestinationType::COM: // Commercial Address
                $r->residentialCode = '0';
                break;
        }
        $this->_request = $r;

        return $this->_request;
    }

    protected function _getQuotes()
    {
        if (Axis_ShippingUps_Model_Option_Standard_RequestType::XML === $this->_config->type) {
            return $this->_getXmlQuotes();
        }
        return $this->_getCgiQuotes();
    }

    /**
     * Sent request for quote to UPS via XML
     *
     * @return array
     */
    protected function _getXmlQuotes()
    {
        $xml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><AccessRequest/>');
        $xml->addAttribute('xml:lang', 'en-US');
        $xml->addChild('AccessLicenseNumber', $this->_config->xmlAccessLicenseNumber);
        $xml->addChild('UserId', $this->_config->xmlUserId);
        $xml->addChild('Password', $this->_config->xmlPassword);

        $this->_xmlAccessRequest = $xml->asXML();

        $xml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><RatingServiceSelectionRequest/>');
        $xml->addAttribute('xml:lang', 'en-US');
        $request = $xml->addChild('Request');
        $transactionReference = $request->addChild('TransactionReference');
        $transactionReference->addChild('CustomerContext', 'Rating and Service');
        $transactionReference->addChild('XpciVersion', '1.0');
        $request->addChild('RequestAction', 'Rate');

        $option = 'Rate';
        if ('4' == $this->_request->actionCode)  {
            $option = 'Shop';
        }
        $request->addChild('RequestOption', $option);

        $pickupType = $xml->addChild('PickupType');

        $pickupType->addChild('Code', $this->_request->pickupCode);
        /*
        '01' (daily pickup), '03' (customer counter), '06' (one time pickup),
        '07' (oncall air), '11' (suggested retail rates),
        '19' (letter center), or '20' (air service center)
        */
        $pickupType->addChild('Description', $this->_request->pickupLabel);
        //$customerClassification = $xml->addChild('CustomerClassification');
        //$customerClassification->addChild('Code',);
        /*string '01' (wholesale), '03' (occasional), or '04' (retail);
         for daily pickups the default is wholesale;
         for customer counter pickups the default is retail;
         for other pickups the default is occasional
        */
        $shipment = $xml->addChild('Shipment');
        $service = $shipment->addChild('Service');
        
        $service->addChild('Code', $this->_request->productCode);
        $code = $this->_request->productCode ?
            $this->_codeToValue[$this->_request->productCode] : '';
        $service->addChild('Description', Axis_ShippingUps_Model_Option_Standard_OriginServiceLabel::getConfigOptionValue($code));
        
        $shipper = $shipment->addChild('Shipper');
        if ($this->_config->negotiatedActive && $this->_config->shipperNumber) {
            $shipper->addChild('<ShipperNumber>', $this->_config->shipperNumber);
        }
        $address = $shipper->addChild('Address');
        $address->addChild('City', $this->_request->originCity);
        $address->addChild('PostalCode', $this->_request->originPostalCode);
        $address->addChild('CountryCode', $this->_request->originCountryCode);
        $address->addChild('StateProvinceCode', $this->_request->originZone);

        $address = $shipment->addChild('ShipTo')->addChild('Address');
        $address->addChild('PostalCode', $this->_request->destPostalCode);
        $address->addChild('CountryCode', $this->_request->destCountryCode);
        $address->addChild('ResidentialAddress', $this->_request->residentialCode);
        $address->addChild('StateProvinceCode', $this->_request->destZone);
        if ('1' === $this->_request->residentialCode) {
            $address->addChild('ResidentialAddressIndicator', $this->_request->residentialCode);
        }

        $address = $shipment->addChild('ShipFrom')->addChild('Address');
        $address->addChild('PostalCode', $this->_request->originPostalCode);
        $address->addChild('CountryCode', $this->_request->originCountryCode);
        $address->addChild('StateProvinceCode', $this->_request->originZone);

        $package = $shipment->addChild('Package');
        $package->addChild('PackagingType')->addChild('Code', $this->_request->containerCode);
        $packageWeight = $package->addChild('PackageWeight');
        $packageWeight->addChild('UnitOfMeasurement')->addChild('Code', $this->_request->weightUnit);
        $packageWeight->addChild('Weight', $this->_request->packageWeight);
        if ($this->_config->negotiatedActive) {
            $rateInformation = $shipment->addChild('RateInformation');
            $rateInformation->addChild('NegotiatedRatesIndicator');
        }

        $xmlRequest = $this->_xmlAccessRequest . $xml->asXML();
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_config->xmlGateway);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $xmlResponse = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            $xmlResponse = '';
        }

        return $this->_parseXmlResponse($xmlResponse);
    }

    /**
     * Parse XML response
     *
     * @param string $response
     * @return array
     */
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

        $xml = simplexml_load_string($response);
        if (!is_object($xml)) {
            $this->log('Can not convert Response to Xml');
            return $methods;
        }

        if ($xml->Response->ResponseStatusCode != "1") {

            $this->log((string)$xml->Response->Error->ErrorDescription);
            return $methods;
        }

        $allowedMethods = $this->_config->types->toArray();

        // Negotiated rates
        $negotiatedArr = $xml->RatedShipment->NegotiatedRates;
        $negotiatedActive = $this->_config->negotiatedActive &&
            $this->_config->shipperNumber && !empty($negotiatedArr);

        foreach ($xml->RatedShipment as $shipElement) {
            $code = $this->_valueToCode[(string)$shipElement->Service->Code];

            if (!in_array($code, $allowedMethods)) {
                continue;
            }

            if ($negotiatedActive) {
                $cost     = (float) $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
                $currency = (string) $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->CurrencyCode;
            } else {
                $cost     = (float) $shipElement->TotalCharges->MonetaryValue;
                $currency = (string) $shipElement->TotalCharges->CurrencyCode;
            }
            $cost = Axis::single('locale/currency')->from($cost, $currency);
            $methods[] = array(
                'id'    => $this->_code . '_' . $code,
                'title' => $this->getTranslator()->__(
                    Axis_ShippingUps_Model_Option_Standard_OriginServiceLabel::getConfigOptionValue(
                        (string)$shipElement->Service->Code
                    )
                ),
                'price' => $cost + $this->_config->handling
            );
        }
        return $methods;
    }

    /**
     * Sent request for quote to UPS via older HTML method
     *
     * @return array
     */
    protected function _getCgiQuotes()
    {
        $request = join('&', array(
            'accept_UPS_license_agreement=yes',
            '10_action='      . $this->_request->actionCode,
            '13_product='     . $this->_request->productCode,
            '14_origCountry=' . $this->_request->originCountryCode,
            '15_origPostal='  . $this->_request->originPostalCode,
            'origCity='       . $this->_request->originCity,
            '19_destPostal='  . $this->_request->destPostalCode,
            '22_destCountry=' . $this->_request->destCountryCode,
            '23_weight='      . $this->_request->packageWeight,
            '47_rate_chart='  . $this->_request->pickupLabel,
            '48_container='   . $this->_request->containerCode,
            '49_residential=' . $this->_request->residentialCode,
            'weight_std='     . $this->_request->weightUnit
        ));
        $httpClient = new Zend_Http_Client();
        $httpClient->setHeaders(array(
            'Host'       => 'www.ups.com',
            'User-Agent' => 'Axis',
            'Connection' => 'Close'
        ));

        $request = str_replace(' ', '+', $request);
        $uri = $this->_defaultGatewayUrl;
        if (!empty($this->_config->gateway)) {
            $uri = $this->_config->gateway;
        }
        $httpClient->setUri($uri . '?'. $request);
        $httpClient->setConfig(array('maxredirects' => 0, 'timeout' => 30));

        try {
            return $this->_parseCgiResponse(
                $httpClient->request('GET')->getBody()
            );
        } catch (Exception $e) {
            $this->log($e->getMessage());
            return array();
        }
    }

    /**
     * Parse Cgi Response
     * @param string $response
     * @return array
     */
    protected function _parseCgiResponse($response)
    {
        $rows = explode("\n", $response);

        $methods = array();
        // @todo while not number boxes
        //        switch ($this->_config->boxWeightDisplay) {
        //            case (0):
        //                $show_box_weight = '';
        //                break;
        //            case (1):
        //                $show_box_weight = ' (' . $this->_request->upsNumberBoxes . ' boxes)';
        //                break;
        //            case (2):
        //                $show_box_weight = ' (' . number_format($this->_request->upsPackageWeight * $this->_request->upsNumberBoxes, 2) . ' weight)';
        //                break;
        //            default:
        //                $show_box_weight = ' (' . $this->_request->upsNumberBoxes . ' x ' . number_format($this->_request->upsPackageWeight, 2) . ' weight)';
        //                break;
        //        }

        $allowedMethods = $this->_config->types->toArray();

        for ($i = 0; $i < sizeof($rows); $i++) {
            $code = null;
            $row = explode('%', $rows[$i]);
            $errcode = substr($row[0], -1);
            switch ($errcode) {
                case 3:
                case 4:
                    $code = $row[1];
                    $cost = $row[10];
                    break;
                case 5:
                    $this->log($row[1]);
                    break;
                case 6:
                    $code = $row[3];
                    $cost = $row[10];
                    break;
            }

            if (!in_array($code, $allowedMethods)) {
                continue;
            }
            $cost = Axis::single('locale/currency')->from($cost, 'USD');
            $methods[] = array(
                'id' => $this->_code . '_' . $code,
                'title' => $this->getTranslator()->__(
                    Axis_ShippingUps_Model_Option_Standard_OriginServiceLabel::getConfigOptionValue(
                        $this->_codeToValue[$code]
                    )
                ) /*. ' ' . $show_box_weight*/,
                'price' => $cost + $this->_config->handling
                // @todo)* $this->_request->numberBoxes
            );

        }
        return $methods;
    }
}