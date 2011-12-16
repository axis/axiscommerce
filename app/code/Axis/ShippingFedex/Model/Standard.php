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
 * @package     Axis_ShippingFedex
 * @subpackage  Axis_ShippingFedex_Model
 * @copyright   Copyright 2008-2009 Axis LLC
 * @copyright   (c) 2002, 2003 Steve Fatula of Fatula Consulting compconsultant@yahoo.com
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingFedex
 * @subpackage  Axis_ShippingFedex_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingFedex_Model_Standard extends Axis_Method_Shipping_Model_Abstract
{
    protected $_code = 'Fedex_Standard';

    protected $_title = 'Fedex';

    protected $_description = 'Fedex Express shipping method';

    protected $_icon = 'fedex=>images/EXPRESS.gif';

    protected $_defaultGatewayUrl = 'https://gateway.fedex.com/GatewayDC';
    //protected $_gatewayUrl = 'https://gateway.fedex.com/web-services';

    public function getAllowedTypes($request)
    {
        $this->_setRequest($request);
        $this->_types = $this->_getXMLQuotes();
        return $this->_types;
    }

    /**
     * Set request params
     * @param array $request
     */
    protected function _setRequest($request)
    {
        $r = new Axis_Object();
        $r->service = implode(',', $this->_config->allowedTypes->toArray());
        $r->account = $this->_config->account;
        $r->dropoff = $this->_config->dropoff;
        $r->package = $this->_config->package;
        $r->measure = $this->_config->measure;

        // Set Origin detail
        $r->originPostalCode = Axis::config()->core->store->zip;

        $r->origStateOrProvinceCode = Axis::single('location/zone')->getCode(
            Axis::config()->core->store->zone
        );

        $r->originCountryCode = Axis::single('location/country')->find(
            Axis::config()->core->store->country
        )->current()->iso_code_2;

        // Set Destination information
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

        $r->weight = $request['weight'];
        $r->value = Axis::single('locale/currency')->to($request['price'], 'USD');
        $r->currency = 'USD';//$request['currency'];
        $this->_request = $r;
        return $this->_request;
    }

    protected function _getXMLQuotes()
    {
        $r = $this->_request;
        
        $xml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><FDXRateAvailableServicesRequest/>');

        $xml->addAttribute('xmlns:api', 'http://www.fedex.com/fsmapi');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'FDXRateAvailableServicesRequest.xsd');

        $requestHeader = $xml->addChild('RequestHeader');
//          $requestHeader->addChild('CustomerTransactionIdentifier', 'CTIString');
        $requestHeader->addChild('AccountNumber', $r->account);
//      $requestHeader->addChild('MeterNumber', '118500832');//  -- my own meter number
        $requestHeader->addChild('MeterNumber', '0');
//          $requestHeader->addChild('CarrierCode', 'FDXE');
//          $requestHeader->addChild('CarrierCode', 'FDXG');
        /**
         *  FDXE - FedEx Express
         *  FDXG - FedEx Ground
         */

        $xml->addChild('ShipDate', date('Y-m-d'));
//      $xml->addChild('ReturnShipmentIndicator', 'NONRETURN');
        /**
         *  - NONRETURN
         *  - PRINTRETURNLABEL
         *  - EMAILLABEL
         */
        $xml->addChild('DropoffType', $r->dropoff);
        /**
         *  - REGULARPICKUP
         *  - REQUESTCOURIER
         *  - DROPBOX
         *  - BUSINESSSERVICECENTER
         *  - STATION
         *  Only REGULARPICKUP, REQUESTCOURIER, and STATION are
         *  allowed with international freight shipping.
         */
        if (!empty($r->service)) {
            $xml->addChild('Service', $r->service);
        }
        /**
         *  One of the following FedEx Services is optional:
         *  - PRIORITYOVERNIGHT
         *  - STANDARDOVERNIGHT
         *  - FIRSTOVERNIGHT
         *  - FEDEX2DAY
         *  - FEDEXEXPRESSSAVER
         *  - INTERNATIONALPRIORITY
         *  - INTERNATIONALECONOMY
         *  - INTERNATIONALFIRST
         *  - FEDEX1DAYFREIGHT
         *  - FEDEX2DAYFREIGHT
         *  - FEDEX3DAYFREIGHT
         *  - FEDEXGROUND
         *  - GROUNDHOMEDELIVERY
         *  - INTERNATIONALPRIORITY FREIGHT
         *  - INTERNATIONALECONOMY FREIGHT
         *  - EUROPEFIRSTINTERNATIONALPRIORITY
         *  If provided, only that service-s estimated charges will be returned.
         */
        $xml->addChild('Packaging', $r->package);
        /**
         *  One of the following package types is required:
         *  - FEDEXENVELOPE
         *  - FEDEXPAK
         *  - FEDEXBOX
         *  - FEDEXTUBE
         *  - FEDEX10KGBOX
         *  - FEDEX25KGBOX
         *  - YOURPACKAGING
         *  If value entered is FEDEXENVELOPE, FEDEX10KGBOX, or
         *  FEDEX25KGBOX, an MPS rate quote is not allowed.
         */
        $xml->addChild('WeightUnits', $r->measure);
        /**
         *  - LBS
         *  - KGS
         *  LBS is required for a U.S. FedEx Express rate quote.orig
         */
        $xml->addChild('Weight', $r->weight);
//      $xml->addChild('ListRate', 'true');
        /**
         *  Optional.
         *  If = true or 1, list-rate courtesy quotes should be returned in addition to
         *  the discounted quote.
         */

        $originAddress = $xml->addChild('OriginAddress');
//          $originAddress->addChild('StateOrProvinceCode', 'GA');   -- ???
        $originAddress->addChild('PostalCode', $r->originPostalCode);
        $originAddress->addChild('CountryCode', $r->originCountryCode);

        $destinationAddress = $xml->addChild('DestinationAddress');
//          $destinationAddress->addChild('StateOrProvinceCode', 'GA');   -- ???
        $destinationAddress->addChild('PostalCode', $r->destPostalCode);
        $destinationAddress->addChild('CountryCode', $r->destCountryCode);

        $payment = $xml->addChild('Payment');
        $payment->addChild('PayorType', 'SENDER');
        /**
         *  Optional.
         *  Defaults to SENDER.
         *  If value other than SENDER is used, no rates will still be returned.
         */

        /**
         *  DIMENSIONS
         *
         *  Dimensions / Length
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The length of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Width
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The width of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Height
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The height of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Units
         *  Required if dimensions are entered.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The valid unit of measure codes for the package dimensions are:
         *  IN - Inches
         *  CM - Centimeters
         *  U.S. FedEx Express must be in inches.
         */

        $declaredValue = $xml->addChild('DeclaredValue');
        $declaredValue->addChild('Value', $r->value);
        $declaredValue->addChild('CurrencyCode', $r->currency);

        if ($this->_config->residenceDelivery) {
            $specialServices = $xml->addChild('SpecialServices');
            $specialServices->addChild('ResidentialDelivery', 'true');
        }

//      $specialServices = $xml->addChild('SpecialServices');
//          $specialServices->addChild('Alcohol', 'true');
//          $specialServices->addChild('DangerousGoods', 'true')->addChild('Accessibility', 'ACCESSIBLE');
        /**
         *  Valid values:
         *  ACCESSIBLE - accessible DG
         *  INACCESSIBLE - inaccessible DG
         */
//          $specialServices->addChild('DryIce', 'true');
//          $specialServices->addChild('ResidentialDelivery', 'true');
        /**
         *  If = true or 1, the shipment is Residential Delivery. If Recipient Address
         *  is in a rural area (defined by table lookup), additional charge will be
         *  applied. This element is not applicable to the FedEx Home Delivery
         *  service.
         */
//          $specialServices->addChild('InsidePickup', 'true');
//          $specialServices->addChild('InsideDelivery', 'true');
//          $specialServices->addChild('SaturdayPickup', 'true');
//          $specialServices->addChild('SaturdayDelivery', 'true');
//          $specialServices->addChild('NonstandardContainer', 'true');
//          $specialServices->addChild('SignatureOption', 'true');
        /**
         *  Optional.
         *  Specifies the Delivery Signature Option requested for the shipment.
         *  Valid values:
         *  - DELIVERWITHOUTSIGNATURE
         *  - INDIRECT
         *  - DIRECT
         *  - ADULT
         *  For FedEx Express shipments, the DELIVERWITHOUTSIGNATURE
         *  option will not be allowed when the following special services are
         *  requested:
         *  - Alcohol
         *  - Hold at Location
         *  - Dangerous Goods
         *  - Declared Value greater than $500
         */

        /**
         *  HOMEDELIVERY
         *
         *  HomeDelivery / Type
         *  One of the following values are required for FedEx Home Delivery
         *  shipments:
         *  - DATECERTAIN
         *  - EVENING
         *  - APPOINTMENT
         *
         *  PackageCount
         *  Required for multiple-piece shipments (MPS).
         *  For MPS shipments, 1 piece = 1 box.
         *  For international Freight MPS shipments, this is the total number of
         *  "units." Units are the skids, pallets, or boxes that make up a freight
         *  shipment.
         *  Each unit within a shipment should have its own label.
         *  FDXE only applies to COD, MPS, and international.
         *  Valid values: 1 to 999
         */

        /**
         *  VARIABLEHANDLINGCHARGES
         *
         *  VariableHandlingCharges / Level
         *  Optional.
         *  Only applicable if valid Variable Handling Type is present.
         *  Apply fixed or variable handling charges at package or shipment level.
         *  Valid values:
         *  - PACKAGE
         *  - SHIPMENT
         *  The value "SHIPMENT" is applicable only on last piece of FedEx
         *  Ground or FedEx Express MPS shipment only.
         *  Note: Value "SHIPMENT" = shipment level affects the entire shipment.
         *  Anything else sent in Child will be ignored.
         *
         *  VariableHandlingCharges / Type
         *  Optional.
         *  If valid value is present, a valid Variable Handling Charge is required.
         *  Specifies what type of Variable Handling charges to assess and on
         *  which amount.
         *  Valid values:
         *  - FIXED_AMOUNT
         *  - PERCENTAGE_OF_BASE
         *  - PERCENTAGE_OF_NET
         *  - PERCENTAGE_OF_NET_ EXCL_TAXES
         *
         *  VariableHandlingCharges / AmountOrPercentage
         *  Optional.
         *  Required in conjunction with Variable Handling Type.
         *  Contains the dollar or percentage amount to be added to the Freight
         *  charges. Whether the amount is a dollar or percentage is based on the
         *  Variable Handling Type value that is included in this Request.
         *  Format: Two explicit decimal positions (e.g. 1.00); 10 total length
         *  including decimal place.
         */

        $xml->addChild('PackageCount', '1');
        $request = $xml->asXML();
        Axis_FirePhp::log($request);
        try {
            $url = $this->_config->gateway;
            if (!$url) {
                $url = $this->_defaultGatewayUrl;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            //FIXME: Without the next 2 options, SSL calls will fail in some OSes where default CA file does not
            //    exist in /etc/ssl/certs/ca-certificates.crt (Curl default). This is a security risk
            //    since the SSL cert will not be verified
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            $response = curl_exec($ch);
            curl_close ($ch);
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

        $xml = simplexml_load_string($response);
        if (!is_object($xml)) {
            $this->log('Can not convert Response to Xml');
            return $methods;
        }

        if (is_object($xml->Error) && is_object($xml->Error->Message)) {
            $this->log((string)$xml->Error->Message);
            return $methods;
        }

        if (is_object($xml->SoftError) && is_object($xml->SoftError->Message)) {
            $this->log((string)$xml->SoftError->Message);
            return $methods;
        } 

        $allowedMethods = $this->_config->allowedTypes->toArray();
        
        foreach ($xml->Entry as $entry) {
            if (!in_array((string)$entry->Service, $allowedMethods)) {
                continue;
            }
            $cost = Axis::single('locale/currency')->from(
                (float)$entry->EstimatedCharges->DiscountedCharges->NetCharge
            );
            $methods[] = array(
                'id' => $this->_code . '_' . (string)$entry->Service,
                'title' => $this->getTranslator()->__(
                    Axis_ShippingFedex_Model_Standard_Service::getConfigOptionName(
                        (string)$entry->Service
                    )
                ),
                'price' => $cost + $this->_config->handling
            );
        }
        return $methods;
    }

    // @TODO http://kernelhack.com/?p=168
    //   protected function _getWSDLQuotes()
//    {
//        $r = $this->_request;
//
//    $newline = "<br>";
//
//    $path_to_wsdl = dirname(__FILE__) . '/../etc/RateService_v7.wsdl';
//    # WSDL path
//        try {
//            ini_set("soap.wsdl_cache_enabled", "0");
//            $client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
//            # Authenticate FedEx Key, and Password
//            $request['WebAuthenticationDetail'] = array('UserCredential' => array(
//                'Key' => '0m3rfedex',
//                'Password' => 'front123'
//            ));
//            # Fedex Account and Meter
//            $request['ClientDetail'] = array(
//                'AccountNumber' => $r->account,
//                'MeterNumber' => '118500832'
//            );
//            $request['TransactionDetail'] = array(
//              'CustomerTransactionId' => ' *** Rate Available Services Request v6 using PHP ***'
//            );
//            $request['Version'] = array(
//                'ServiceId' => 'crs',
//                'Major' => '7',
//                'Intermediate' => '0',
//                'Minor' => '0'
//            );
//
//            # DropoffType, ShipTimestamp, and PackagingType
//            $request['RequestedShipment']['DropoffType'] = $r->dropoff; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
//
//            $request['RequestedShipment']['ShipTimestamp'] = date('c');
//
//            $request['RequestedShipment']['PackagingType'] = $r->package;
//
//            if ($third_party) {
//                $shipping_charges_payment = array(
//                    'PaymentType' => 'THIRD_PARTY',
//                    'Payor' => array(
//                        'AccountNumber' => 'third_party_fedex_account',
//                        'CountryCode' => 'third_party_fedex_account_country'
//                ));
//            } else {
//                $shipping_charges_payment = array(
//                    'PaymentType' => 'SENDER',
//                    'Payor' => array(
//                        'AccountNumber' => 'account',
//                        'CountryCode' => 'account_country'
//                ));
//            }
//
//            # Set Shipper and Recipient
//            $request['RequestedShipment']['Shipper'] = array(
//                'Address' => array(
//                    'StreetLines' => array('street1', /*''*/), #Origin details
//                    'City' => Axis::config()->core->store->city,
//                    'StateOrProvinceCode' => $r->origStateOrProvinceCode,
//                    'PostalCode' => $r->originPostalCode,
//                    'CountryCode' => $r->originCountryCode
//                )
//            );
//
//            $request['RequestedShipment']['Recipient'] = array(
//                'Address' => array(
//                    'PostalCode' => $r->destPostalCode,
//                    'CountryCode' => $r->destCountryCode
//                )
//            );
//
//            $request['RequestedShipment']['ShippingChargesPayment'] = $shipping_charges_payment;
//            $request['RequestedShipment']['RateRequestTypes'] = 'LIST';
//            $request['RequestedShipment']['PackageCount'] = '1';
//            $request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
//
//            $request['RequestedShipment']['RequestedPackages'] = array(
//                '0' => array(
//                    'SequenceNumber' => '1',
//                    'InsuredValue' => array(
//                        'Amount' => $r->value,
//                        'Currency' => $r->currency
//                    ),
//                    'Weight' => array(
//                        'Value' => $r->weight,
//                        'Units' => 'LB'
//                    )
//                )
//            );
//            $response = $client->getRates($request);
//            Zend_Debug::dump($response);
//            die();
//
//        } catch (SoapFault $fault) {
//
//        }
//
//
//        return array();
//    }
}