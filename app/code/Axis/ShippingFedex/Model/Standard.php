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

    protected $_defaultGatewayUrl = 'https://gateway.fedex.com:443/GatewayDC';
// test https://wsbeta.fedex.com:443/web-services
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
        $r->service = implode(',', $this->_config->allowedTypes->toArray());
        
        $r->key = 'FzwoXkfuIpL7J9Rg';//$this->_config->key;
        $r->password = 'B8g1a0Q5Gp3XZXjSdA1wOR08Z';//$this->_config->password;
        
        $r->accountNumber = '510087569';//$this->_config->account;
        $r->meterNumber = '118558968';//$this->_config->meter_number; 
        
        $r->dropoffType = $this->_config->dropoff;
        $r->packaging = $this->_config->package;
        $r->measure = $this->_config->measure;

        // Set Origin detail
        $r->originPostalCode = Axis::config()->core->store->zip;

        $r->origStateOrProvinceCode = Axis::single('location/zone')->getCode(
            Axis::config('core/store/zone')
        );
        
        $r->originCountryCode = Axis::single('location/country')->find(
            Axis::config('core/store/country')
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
        $r->currencyCode = 'USD';//$request['currency'];
        
        $this->_request = $r;
        
        return $this->_request;
    }

    protected function _getQuotes()
    {
        $r = $this->_request;
        
        $request = array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key'      => $r->key,
                    'Password' => $r->password
                )
            ),
            'ClientDetail' => array(
                'AccountNumber' => $r->accountNumber,
                'MeterNumber'   => $r->meterNumber
            ),
            'TransactionDetail' => array(
                "CustomerTransactionId" => " *** Rate Available Services Request v10 using PHP ***"
            ),
            'Version' => array('ServiceId' => 'crs', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0'),
            "ReturnTransitAndCommit" => true,
            'RequestedShipment' => array(
                'DropoffType'   => 'REGULAR_PICKUP',//$r->dropoffType,
                'ShipTimestamp' => date('c'),
//                'ServiceType'   => 'STANDARD_OVERNIGHT',
                'PackagingType' => 'YOUR_PACKAGING',//$r->packaging,
                'TotalInsuredValue' => array(
                    'Ammount'  => $r->value,
                    'Currency' => $r->currencyCode
                ),
                'Shipper' => array(
                    'Address' => array(
                            'PostalCode' => $r->originPostalCode,
                            'CountryCode' => $r->originCountryCode
                    )
                ),
                'Recipient' => array(
                        'Address' => array(
                                'PostalCode' => 'V7C4V4',
                                'CountryCode' => 'CA',
//                                'PostalCode' => $r->destPostalCode,
//                                'CountryCode' => $r->destCountryCode,
                                'Residential' => (bool)$this->_config->residenceDelivery
                        )
                ),
                'ShippingChargesPayment' => array(
                    'PaymentType' => 'SENDER',
                    'Payor' => array(
                        'AccountNumber' => $r->accountNumber,
                        'CountryCode'   => $r->originCountryCode
                    )
                ),
                'RateRequestTypes' => 'LIST',
                'PackageCount'     => '1',
                'PackageDetail'    => 'INDIVIDUAL_PACKAGES',
                'RequestedPackageLineItems' =>  array(
                    array(
                        'GroupPackageCount'=>1,
                        'Weight' => array(
                            'Value' => (float)$r->weight,
                            'Units' => 'LB'
                        )
                    )
                )
            )
        );
        $path_to_wsdl = Axis::config('system/path') . "/app/code/Axis/ShippingFedex/etc/RateService_v10.wsdl";
        
        $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $client->__setLocation('https://wsbeta.fedex.com:443/web-services/rate');
	
        $response = $client->getRates($request);

        return $this->_parseResponse($response);
    }

    protected function _parseResponse($response)
    {
        $rates = array();
        if (!is_object($response)) {
            return $rates;
        }
        
        if ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR') {
            
            $this->log((string)$response->Notifications->Message);
            return $rates;
        }
        if (!isset($response->RateReplyDetails)) {
            return $rates;
        }
        
        $_rates = $response -> RateReplyDetails;
        if (!is_array($_rates)) {
            $_rates = array($_rates);
        }

        $allowedServiceType = $this->_config->allowedTypes->toArray();
        $services = Axis::model('ShippingFedex/Option_Standard_Service');
        
        $handling = $this->_config->handling;
        foreach ($_rates as $rate) {
            $service = (string)$rate->ServiceType;
            if (!in_array($service, $allowedServiceType)) {
                continue;
            }
            $amount = Axis::single('locale/currency')->from(
                (float)$rate->RatedShipmentDetails[0]
                                ->ShipmentRateDetail->TotalNetCharge->Amount
            ) ;
            
            $rates[] = array(
                'id'    => $this->_code . '_' . $service,
                'title' => $service,//$this->getTranslator()->__($services[$service]),
                'price' => $amount + $handling
            );
        }
        return $rates;
    }
}