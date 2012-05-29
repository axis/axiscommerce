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

        $r->key      = $this->_config->key;
        $r->password = $this->_config->password;

        $r->accountNumber = $this->_config->account;
        $r->meterNumber   = $this->_config->meterNumber;

        $r->dropoffType = $this->_config->dropoff;
        $r->packaging   = $this->_config->package;
        $r->measure     = $this->_config->measure;

        // Set Origin detail
        $r->originPostalCode = Axis::config()->core->store->zip;

        $r->origStateOrProvinceCode = Axis::single('location/zone')->getCode(
            Axis::config('core/store/zone')
        );

        $r->originCountryCode = Axis::single('location/country')->find(
            Axis::config('core/store/country')
        )->current()->iso_code_2;

        // Set Destination information
        $r->destPostalCode = substr(
            str_replace(' ', '', $request['postcode']),
            0,
            $request['country']['iso_code_2'] == 'US' ? 5 : 6
        );

        $r->destCountryCode = $request['country']['iso_code_2'];

        $r->weight = (float)$request['weight'];
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
            'Version' => array(
                'ServiceId'    => 'crs',
                'Major'        => '10',
                'Intermediate' => '0',
                'Minor'        => '0'
            ),
//            "ReturnTransitAndCommit" => true,
            'RequestedShipment' => array(
                'DropoffType'   => $r->dropoffType,
                'ShipTimestamp' => date('c'),
//                'ServiceType'   => 'STANDARD_OVERNIGHT',
                'PackagingType' => $r->packaging,
                'TotalInsuredValue' => array(
                    'Ammount'  => $r->value,
                    'Currency' => $r->currencyCode
                ),
                'Shipper' => array(
                    'Address' => array(
                        'PostalCode'  => $r->originPostalCode,
                        'CountryCode' => $r->originCountryCode
                    )
                ),
                'Recipient' => array(
                    'Address' => array(
                        'PostalCode'  => $r->destPostalCode,
                        'CountryCode' => $r->destCountryCode,
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
                        'GroupPackageCount' => 1,
                        'Weight' => array(
                            'Value' => $r->weight,
                            'Units' => 'LB'
                        )
                    )
                )
            )
        );
        $pathToWsdl = Axis::config('system/path') . "/app/code/Axis/ShippingFedex/etc/RateService_v10.wsdl";

        $client = new SoapClient($pathToWsdl);
//        production : https://gateway.fedex.com:443/GatewayDC
//        test       : https://wsbeta.fedex.com:443/web-services
        $client->__setLocation($this->_config->gateway);

        $response = $client->getRates($request);

        return $this->_parseResponse($response);
    }

    protected function _parseResponse($response)
    {
        $rates = array();
        if (!is_object($response)) {
            $this->log('Unable to retrieve quotes');
            return $rates;
        }

        if ($response->HighestSeverity == 'FAILURE'
            || $response->HighestSeverity == 'ERROR') {

            $this->log((string)$response->Notifications->Message);
            return $rates;
        }
        if (!isset($response->RateReplyDetails)) {
            return $rates;
        }

        $_rates = $response->RateReplyDetails;
//        if (!is_array($_rates)) {
//            $_rates = array($_rates);
//        }

        $allowedServiceType = $this->_config->allowedTypes->toArray();
        $serviceLabels = Axis::model('ShippingFedex/Option_Standard_Service');

        $handling = $this->_config->handling;
        foreach ($_rates as $rate) {
            $serviceType = (string)$rate->ServiceType;
            if (!in_array($serviceType, $allowedServiceType)) {
                continue;
            }
            $amount = (float)$rate->RatedShipmentDetails[0]
                ->ShipmentRateDetail->TotalNetCharge->Amount;
            $amount = Axis::single('locale/currency')->from($amount, 'USD') ;
            $title = $this->getTranslator()->__($serviceLabels[$serviceType]);

            $rates[] = array(
                'id'    => $this->_code . '_' . $serviceType,
                'title' => $title,
                'price' => $amount + $handling
            );
        }
        return $rates;
    }
}