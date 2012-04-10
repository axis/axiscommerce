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
 * @package     Axis_PaymentAuthorizenetAim
 * @subpackage  Axis_PaymentAuthorizenetAim_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_PaymentAuthorizenetAim
 * @subpackage  Axis_PaymentAuthorizenetAim_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_PaymentAuthorizenetAim_Model_Standard extends Axis_Method_Payment_Model_Card_Abstract
{
    const APPROVED  = 1;
    const DECLINED  = 2;
    const ERROR     = 3;
    const HELD      = 4;

    protected $_code = 'AuthorizenetAim_Standard';
    protected $_title = 'AuthorizenetAim';
    protected $_request;

    /**
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function postProcess(Axis_Sales_Model_Order_Row $order)
    {
        if (Axis_PaymentAuthorizenetAim_Model_Option_Standard_AuthorizationType::AUTHORIZE === $this->_config->authorizationType) {
            $response = $this->_authorize($order);
        } else {
            $response = $this->_capture($order);
        }

        if (self::APPROVED != $response->getResponseCode()) {
            $message = sprintf(
                "Authorize.Net Error:\n\tResponse Reason Code: %s;\n\tResponse Code: %s;\n\tResponse Reson Text: %s",
                $response->getResponseReasonCode(),
                $response->getResponseCode(),
                $response->getResponseReasonText()
            );
            $order->setStatus('failed', $message);
            $this->log($message);
            throw new Axis_Exception($response->getResponseReasonText());
        }
        $this->getRequest()->setLastTransId($response->getTransactionId());

        $crypt = Axis_Crypt::factory();
        $cc = $this->getCreditCard();
        $ccNumber = $cc->getCcNumber();
        $ccNumber = substr($ccNumber, 0, 4)
                  . str_repeat('X', (strlen($ccNumber) - 8))
                  . substr($ccNumber, -4);

        Axis::single('paymentAuthorizenetAim/standard_order')->insert(array(
            'order_id'   => $order->id,
            'trans_id'   => $this->getRequest()->getLastTransId(),
            'cc_type'    => $crypt->encrypt($cc->getCcType()),
            'cc_owner'   => $crypt->encrypt($cc->getCcOwner()),
            'cc_number'  => $crypt->encrypt($ccNumber),
            'cc_expires' => $crypt->encrypt($cc->getCcExpiresMonth() . '/' . $cc->getCcExpiresYear()),
            'cc_issue'   => $crypt->encrypt($cc->getCcIssueMonth() . '/' . $cc->getCcIssueYear()),
            'x_type'     => $this->getRequest()->getAnetTransType(),
            'x_method'   => $this->getRequest()->getAnetTransMethod()
        ));
    }

    /**
     * Send authorize request to gateway
     *
     * @param   Axis_Sales_Model_Order_Row $order
     * @return  Axis_Object Authorize.Net response
     */
    protected function _authorize(Axis_Sales_Model_Order_Row $order)
    {
        $this->getRequest()->setAnetTransType('AUTH_ONLY');
        return $this->_postRequest(
            $this->_buildRequest($order)
        );
    }

    /**
     * Send authorize and capture request to gateway
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return Axis_Object Authorize.Net response
     */
    protected function _capture(Axis_Sales_Model_Order_Row $order)
    {
        $this->getRequest()->setAnetTransType('AUTH_CAPTURE');
        return $this->_postRequest(
            $this->_buildRequest($order)
        );
    }

    /**
     *  Callback setStatus('failed');
     *
     * @see Axis_Sales_Model_Status_Run
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function failed(Axis_Sales_Model_Order_Row $order)
    {
        return $this->_void($order);
    }

    /**
     *  Callback setStatus('cancel');
     *
     * @see Axis_Sales_Model_Status_Run
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function cancel(Axis_Sales_Model_Order_Row $order)
    {
        return $this->_void($order);
    }

    /**
     *  Callback setStatus('refund');
     *
     * @see Axis_Sales_Model_Status_Run
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    // @todo create form on
    public function refund(Axis_Sales_Model_Order_Row $order)
    {
        //$this->getCreditCard()->setCcNumber(****) etc
        return $this->_credit($order);
    }

    protected function _void(Axis_Sales_Model_Order_Row $order)
    {
        $message = 'Invalid transaction id';
        $transactionId = Axis::single('paymentAuthorizenetAim/standard_order')
            ->getTransactionId($order->id);
        if (null != $transactionId) {
            $this->getRequest()->setAnetTransType('VOID');
            $request = $this->_buildRequest($order);
            $request->setXTransId($transactionId);
            $result = $this->_postRequest($request);

            if($result->getResponseCode() == 1) {
                return true;
            }
            $message = $result->getResponseReasonText();

        }
        $this->log($message);
        return false;
    }

    protected function _credit(Axis_Sales_Model_Order_Row $order)
    {
        $message = 'Error in refunding the payment';
        $transactionId = Axis::single('paymentAuthorizenetAim/standard_order')
            ->getTransactionId($order->id);
        if (null != $transactionId) {
            $this->getRequest()->setAnetTransType('CREDIT');
            $request = $this->_buildRequest($order);
            $request->setXTransId($transactionId);
            $result = $this->_postRequest($request);

            if ($result->getResponseCode() == 1) {
                return true;
            }
            $message = $result->getResponseReasonText();

        }
        $this->log($message);
        return false;
    }

    /**
     * Prepare request to gateway
     *
     * @link http://www.authorize.net/support/AIM_guide.pdf
     * @param Axis_Sales_Model_Order_Row $order
     * @return Axis_Object
     */
    protected function _buildRequest(Axis_Sales_Model_Order_Row $order)
    {
        $request = $this->getRequest();

        if (!$request->getAnetTransMethod()) {
            $request->setAnetTransMethod('CC');
        }

        $request->setXVersion(3.1)
            ->setXDelimData('True')
            ->setXDelimChar(',')
            ->setXRelayResponse('False');

        // @what is getIncrementId
        if ($order->id) {
            $request->setXInvoiceNum($order->id); //use orderId instead invoice number
        }

        $request->setXTestRequest($this->_config->test ? 'TRUE' : 'FALSE') ;

        $request->setXLogin($this->_config->xLogin)
            ->setXTranKey($this->_config->xTransactionKey)
            ->setXType($request->getAnetTransType())
            ->setXMethod($request->getAnetTransMethod());

        if ($order->getAmount()) {
            $request->setXAmount(Axis::single('locale/currency')->convert(
                $order->getAmount(),
                $order->currency,
                Axis::config('locale/main/baseCurrency')
            ));
            $request->setXCurrencyCode(
                Axis::config('locale/main/baseCurrency')
            );
        }

        switch ($request->getAnetTransType()) {
            case 'CREDIT':
            case 'VOID':
            case 'PRIOR_AUTH_CAPTURE':
                $request->setXTransId($request->getCcTransId());
                break;

//            case 'CAPTURE_ONLY':
//                $request->setXAuthCode($request->getCcAuthCode());
//                break;
        }

        $billing = $order->getBilling();
        $request->setXFirstname($billing->getFirstname())
            ->setXLastname($billing->getLastname())
            ->setXCompany($billing->getCompany())
            ->setXAddress($billing->getStreetAddress())
            ->setXCity($billing->getCity())
            ->setXState($billing->getZone()->getName())
            ->setXZip($billing->getPostcode())
            ->setXCountry($billing->getCountry()->getName())
            ->setXPhone($billing->getPhone())
            ->setXFax($billing->getFax())
            ->setXCustId($billing->getCustomerId())
            ->setXCustomerIp($order->getIp())
            ->setXCustomerTaxId($billing->getTaxId())
            ->setXEmail($order->getCustomerEmail())
            ->setXEmailCustomer($this->_config->emailCustomer ? 'TRUE' : 'FALSE')
            ->setXMerchantEmail($this->_config->emailMerchant);

        $delivery = $order->getDelivery();
        $request->setXShipToFirstname($delivery->getFirstname())
            ->setXShipToLastname($delivery->getLastname())
            ->setXShipToCompany($delivery->getCompany())
            ->setXShipToAddress($delivery->getStreetAddress())
            ->setXShipToCity($delivery->getCity())
            ->setXShipToState($delivery->getZone()->getName())
            ->setXShipToZip($delivery->getPostcode())
            ->setXShipToCountry($delivery->getCountry()->getName());

        $request->setXPoNum($order->id)
            ->setXTax($order->getTaxAmount())
            ->setXFreight($order->getShipping());

        switch ($request->getAnetTransMethod()) {
            case 'CC':
                $cc = $this->getCreditCard();
                if ($cc->getCcNumber()) {
                    $request->setXCardNum($cc->getCcNumber())
                        ->setXExpDate(sprintf(
                            '%02d-%02d',
                            $cc->getCcExpiresMonth(),
                            $cc->getCcExpiresYear()
                        ))
                        ->setXCardCode($cc->getCcCvv());
                }
                break;

//            case 'ECHECK':
//                $echeck = $this->getEcheck();
//                $request->setXBankAbaCode($echeck->getEcheckRoutingNumber())
//                    ->setXBankName($echeck->getEcheckBankName())
//                    ->setXBankAcctNum($echeck->getEcheckAccountNumber())
//                    ->setXBankAcctType($echeck->getEcheckAccountType())
//                    ->setXBankAcctName($echeck->getEcheckAccountName())
//                    ->setXEcheckType($echeck->getEcheckType());
//                break;
        }
        $this->_request = $request;
        return $request;
    }

    /**
     * Send request and parse response
     *
     * @param Axis_Object $request
     * @return Axis_Object
     */
    protected function _postRequest(Axis_Object $request)
    {
        $result = new Axis_Object();

        $httpClient = new Zend_Http_Client();
        $httpClient->setUri($this->_config->gateway);
        $httpClient->setConfig(array(
            'maxredirects' => 0,
            //'ssltransport' => 'tcp',
            'timeout' => 30
        ));
        $params = array_diff_key(
            $request->getData(),
            array_fill_keys(array('anet_trans_type', 'last_trans_id', 'cc_trans_id'), 1)
        );
        $httpClient->setParameterPost($params);
        $httpClient->setMethod(Zend_Http_Client::POST);

        try {
            $response = $httpClient->request()->getBody();

        } catch (Exception $e) {
            $message = $e->getCode() . 'Gateway request error: ' . $e->getMessage();
            $this->log($message);
            throw new Axis_Exception($message);
        }

        $r = explode(',', $response);
        if (!$r){
            throw new Axis_Exception('Error in payment gateway');
        }
        $result->setResponseCode((int)str_replace('"','', $r[0]))
            ->setResponseSubcode((int)str_replace('"','', $r[1]))
            ->setResponseReasonCode((int)str_replace('"','', $r[2]))
            ->setResponseReasonText($r[3])
            ->setApprovalCode($r[4])
            ->setAvsResultCode($r[5])
            ->setTransactionId($r[6])
            ->setInvoiceNumber($r[7])
            ->setDescription($r[8])
            ->setAmount($r[9])
            ->setMethod($r[10])
            ->setTransactionType($r[11])
            ->setCustomerId($r[12])
            ->setMd5Hash($r[37])
            ->setCardCodeResponseCode($r[39]);

        return $result;
    }

//    //@todo
//    public function  getEcheck()
//    {
//        return new Axis_Object();
//    }

    public function getRequest()
    {
        if (null === $this->_request) {
            $this->_request = new Axis_Object();
        }
        return $this->_request;
    }
}
