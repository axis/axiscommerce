<?php 
/**
 * paypal_curl.php communications class for Paypal Express Checkout / Website Payments Pro / Payflow Pro payment methods
 *
 * @category    Axis
 * @package     Axis_PaymentPaypal
 * @subpackage  Axis_PaymentPaypal_Model
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: paypal_curl.php 7558 2007-11-30 17:54:43Z drbyte $
 */
 
 /**
 * PayPal NVP (v3.2) and Payflow Pro (v4 HTTP API) implementation via cURL.
 *
 * @category    Axis
 * @package     Axis_PaymentPaypal
 * @subpackage  Axis_PaymentPaypal_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_PaymentPaypal_Model_Api_Nvp 
{
    /**
     * Debug or production?
     */
    private $_server = null;
    
    /**
     * URL endpoints -- defaults here are for three-token NVP implementation
     */
    private $_endpoints = array(
        'live'      => 'https://api-3t.paypal.com/nvp',
        'sandbox'   => 'https://api-3t.sandbox.paypal.com/nvp' /*'https://api.sandbox.paypal.com/nvp'*/
    );

    /**
     * Options for cURL. Defaults to preferred (constant) options.
     */
    private $_curlOptions = array(
        CURLOPT_HEADER          => 0, 
        CURLOPT_RETURNTRANSFER  => 1, 
        CURLOPT_TIMEOUT         => 60, 
        CURLOPT_FOLLOWLOCATION  => 0, 
        CURLOPT_SSL_VERIFYPEER  => 0, 
        CURLOPT_SSL_VERIFYHOST  => 2, 
        CURLOPT_FORBID_REUSE    => true, 
        CURLOPT_POST            => 1
    );
    
    /**
     * Parameters that are always required and that don't change
     * request to request.
     */
    private $_partner;
    private $_vendor;
    private $_user;
    private $_pwd;
    private $_version;
    private $_signature;
    
    /**
     * nvp or payflow?
     */
    private $_mode = 'nvp';
    
    /**
     * Sales or authorizations? For the U.K. this will always be 'S'
     * (Sale) because of Switch and Solo cards which don't support
     * authorizations. The other option is 'A' for Authorization.
     */
    private $_trxtype = 'S';
    
    /**
     * Store the last-generated name/value list for debugging.
     */
    private $lastParamList = null;
    
    /**
     * Store the last-generated headers for debugging.
     */
    private $lastHeaders = null;

    /**
     * Constructor. Sets up communication infrastructure.
     */
    public function __construct($code = 'Paypal_Express') 
    {
        if (Axis::config()->payment->{$code}->mode == 'payflow') {
            $this->_mode = 'payflow';
            $this->_user = trim(Axis::config()->payment->payflow->pfuser);
            $this->_vendor = trim(Axis::config()->payment->payflow->pfvendor);
            $this->_partner = trim(Axis::config()->payment->payflow->pfpartner);
            $this->_pwd = trim(Axis::config()->payment->payflow->pfpassword);
        } else {
            $this->_mode = 'nvp';
            $this->_user = trim(Axis::config()->payment->nvp->apiusername);
            $this->_pwd = trim(Axis::config()->payment->nvp->apipassword);
            $this->_signature = trim(Axis::config()->payment->nvp->apisignature);
        }
        $this->_server = null === Axis::config()->payment->{$code}->server ?
            'sandbox' : Axis::config()->payment->{$code}->server;
        $this->_version = Axis::config()->payment->nvp->version;

    }
    
    /**
     * SetExpressCheckout
     *
     * Prepares to send customer to PayPal site so they can
     * log in and choose their funding source and shipping address.
     *
     * The token returned to this function is passed to PayPal in
     * order to link their PayPal selections to their cart actions.
     */
    public function SetExpressCheckout($amount, $returnUrl, $cancelUrl, $optional = array()) 
    {
        $values = array_merge($optional, array(
            'AMT' => $amount,
            'RETURNURL' => urlencode($returnUrl),
            'CANCELURL' => urlencode($cancelUrl)
        ));
        if ($this->_mode == 'payflow') {
            $values = array_merge($values, array(
                'ACTION' => 'S', /* ACTION=S denotes SetExpressCheckout */
                'TENDER' => 'P',
                'TRXTYPE' => $this->_trxtype,
                'RETURNURL' => $returnUrl,
                'CANCELURL' => $cancelUrl
            ));
        } elseif ($this->_mode == 'nvp') {
            if (!isset($values['PAYMENTACTION']))
                $values['PAYMENTACTION'] = ($this->_trxtype == 'S' ? 'Sale' : 'Authorization');
        }
        /*
         // allow page-styling support -- see language file for definitions
         if (defined('PAGE_STYLE'))   $values['PAGESTYLE'] = PAGE_STYLE;
         if (defined('HEADER_IMAGE')) $values['HDRIMG'] = urlencode(HEADER_IMAGE);
         if (defined('PAGECOLOR'))    $values['PAYFLOWCOLOR'] = PAGECOLOR;
         if (defined('HEADER_BORDER_COLOR')) $values['HDRBORDERCOLOR'] = HEADER_BORDER_COLOR;
         if (defined('HEADER_BACK_COLOR')) $values['HDRBACKCOLOR'] = HEADER_BACK_COLOR;
         */
        return $this->_request($values, 'SetExpressCheckout');
    }
    
    /**
     * GetExpressCheckoutDetails
     *
     * When customer returns from PayPal site, this retrieves their payment/shipping data for use in Zen Cart
     */
    public function GetExpressCheckoutDetails($token, $optional = array()) 
    {
        $values = array_merge($optional, array('TOKEN' => $token));
        if ($this->_mode == 'payflow') {
            $values = array_merge($values, array(
                'ACTION' => 'G', /* ACTION=G denotes GetExpressCheckoutDetails */
                'TENDER' => 'P',
                'TRXTYPE' => $this->_trxtype
            ));
        } elseif ($this->_mode == 'nvp') {
            $values = array_merge($values, array('REQBILLINGADDRESS' => '1'));
        }
        return $this->_request($values, 'GetExpressCheckoutDetails');
    }
    
    /**
     * DoExpressCheckoutPayment
     *
     * Completes the sale using PayPal as payment choice
     */
    public function DoExpressCheckoutPayment($token, $payerId, $amount, $optional = array()) 
    {
        $values = array_merge($optional, array(
            'TOKEN' => $token,
            'PAYERID' => $payerId,
            'AMT' => $amount
        ));
        if ($this->_mode == 'payflow') {
            $values = array_merge($values, array(
                'ACTION' => 'D', /* ACTION=D denotes DoExpressCheckoutPayment */
                'TENDER' => 'P',
                'TRXTYPE' => $this->_trxtype
            ));
        } elseif ($this->_mode == 'nvp') {
            if (!isset($values['PAYMENTACTION'])) {
                $values['PAYMENTACTION'] = ($this->_trxtype == 'S' ? 'Sale' : 'Authorization');
            }
            $view = Axis::app()->getBootstrap()->getResource('layout')->getView();
            $values['NOTIFYURL'] = urlencode($view->href('paymentpaypal/express', true));
        }
        return $this->_request($values, 'DoExpressCheckoutPayment');
    }
    
    /**
     * DoDirectPayment
     * Sends CC information to gateway for processing.
     *
     * Requires Website Payments Pro or Payflow Pro as merchant gateway.
     *
     * PAYMENTACTION = Authorization (auth/capt) or Sale (final)
     */
    public function DoDirectPayment($amount, $cc, $cvv2 = '', $exp, $fname = null, $lname = null, $cc_type, $options = array(), $nvp = array()) 
    {
        $values = $options;
        $values['AMT'] = $amount;
        $values['ACCT'] = $cc;
        if ($cvv2 != '')
            $values['CVV2'] = $cvv2;
            
        if ($this->_mode == 'payflow') {
            $values['EXPDATE'] = $exp;
            $values['TENDER'] = 'C';
            $values['TRXTYPE'] = $this->_trxtype;
            $values['VERBOSITY'] = 'MEDIUM';
            if ((null !== $fname . $lname) && !isset($values['NAME'])) {
                $values['NAME'] = $fname . ' ' . $lname;
            }
        } elseif ($this->_mode == 'nvp') {
            $values = array_merge($values, $nvp);
            $values['CREDITCARDTYPE'] = ($cc_type == 'American Express') ? 'Amex' : $cc_type;
            $values['FIRSTNAME'] = $fname;
            $values['LASTNAME'] = $lname;
            $view = Axis::app()->getBootstrap()->getResource('layout')->getView();
            $values['NOTIFYURL'] = urlencode($view->href('paymentpaypal/express'));
            if (!isset($values['PAYMENTACTION']))
                $values['PAYMENTACTION'] = ($this->_trxtype == 'S' ? 'Sale' : 'Authorization');
                
            if (isset($values['COUNTRY']))
                unset($values['COUNTRY']);
            if (isset($values['NAME']))
                unset($values['NAME']);
            if (isset($values['COMMENT1']))
                unset($values['COMMENT1']);
            if (isset($values['COMMENT2']))
                unset($values['COMMENT2']);
            if (isset($values['CUSTREF']))
                unset($values['CUSTREF']);
        }
        ksort($values);
        return $this->_request($values, 'DoDirectPayment');
    }
    
    /**
     * RefundTransaction
     *
     * Used to refund all or part of a given transaction
     */
    public function RefundTransaction($oID, $txnID, $amount = 'Full', $note = '') 
    {
        if ($this->_mode == 'payflow') {
            $values['ORIGID'] = $txnID;
            $values['TENDER'] = 'C';
            $values['TRXTYPE'] = 'C';
            $values['AMT'] = number_format((float) $amount, 2);
            if ($note != '')
                $values['COMMENT2'] = $note;
        } elseif ($this->_mode == 'nvp') {
            $values['TRANSACTIONID'] = $txnID;
            if ($amount != 'Full' && (float) $amount > 0) {
                $values['REFUNDTYPE'] = 'Partial';
                $values['AMT'] = number_format((float) $amount, 2);
            } else {
                $values['REFUNDTYPE'] = 'Full';
            }
            if ($note != '')
                $values['NOTE'] = $note;
        }
        return $this->_request($values, 'RefundTransaction');
    }
    
    /**
     * DoVoid
     *
     * Used to void a previously authorized transaction
     */
    public function DoVoid($txnID, $note = '') 
    {
        if ($this->_mode == 'payflow') {
            $values['ORIGID'] = $txnID;
            $values['TENDER'] = 'C';
            $values['TRXTYPE'] = 'V';
            if ($note != '')
                $values['COMMENT2'] = $note;
        } elseif ($this->_mode == 'nvp') {
            $values['AUTHORIZATIONID'] = $txnID;
            if ($note != '')
                $values['NOTE'] = $note;
        }
        return $this->_request($values, 'DoVoid');
    }
    
    /**
     * DoAuthorization
     *
     * Used to authorize part of a previously placed order which was initiated as authType of Order
     */
    public function DoAuthorization($txnID, $amount = 0, $currency = 'USD', $entity = 'Order') 
    {
        $values['TRANSACTIONID'] = $txnID;
        $values['AMT'] = number_format($amount, 2, '.', ',');
        $values['TRANSACTIONENTITY'] = $entity;
        $values['CURRENCYCODE'] = $currency;
        return $this->_request($values, 'DoAuthorization');
    }
    
    /**
     * DoReauthorization
     *
     * Used to reauthorize a previously-authorized order which has expired
     */
    public function DoReauthorization($txnID, $amount = 0, $currency = 'USD') 
    {
        $values['AUTHORIZATIONID'] = $txnID;
        $values['AMT'] = number_format($amount, 2, '.', ',');
        $values['CURRENCYCODE'] = $currency;
        return $this->_request($values, 'DoReauthorization');
    }
    
    /**
     * DoCapture
     *
     * Used to capture part or all of a previously placed order which was only authorized
     */
    public function DoCapture($txnID, $amount = 0, $currency = 'USD', $captureType = 'Complete', $invNum = '', $note = '') 
    {
        if ($this->_mode == 'payflow') {
            $values['ORIGID'] = $txnID;
            $values['TENDER'] = 'C';
            $values['TRXTYPE'] = 'D';
            $values['VERBOSITY'] = 'MEDIUM';
            if ($invNum != '')
                $values['INVNUM'] = $invNum;
            if ($note != '')
                $values['COMMENT2'] = $note;
        } elseif ($this->_mode == 'nvp') {
            $values['AUTHORIZATIONID'] = $txnID;
            $values['COMPLETETYPE'] = $captureType;
            $values['AMT'] = number_format((float) $amount, 2);
            $values['CURRENCYCODE'] = $currency;
            if ($invNum != '')
                $values['INVNUM'] = $invNum;
            if ($note != '')
                $values['NOTE'] = $note;
        }
        return $this->_request($values, 'DoCapture');
    }
    
    /**
     * GetTransactionDetails
     *
     * Used to read data from PayPal for a given transaction
     */
    public function GetTransactionDetails($txnID) 
    {
        if ($this->_mode == 'payflow') {
            $values['ORIGID'] = $txnID;
            $values['TENDER'] = 'C';
            $values['TRXTYPE'] = 'I';
            $values['VERBOSITY'] = 'MEDIUM';
        } elseif ($this->_mode == 'nvp') {
            $values['TRANSACTIONID'] = $txnID;
        }
        return $this->_request($values, 'GetTransactionDetails');
    }
    
    /**
     * TransactionSearch
     *
     * Used to read data from PayPal for specified transaction criteria
     */
    public function TransactionSearch($startdate, $txnID = '', $email = '', $options) 
    {
        if ($this->_mode == 'payflow') {
            $values['CUSTREF'] = $txnID;
            $values['TENDER'] = 'C';
            $values['TRXTYPE'] = 'I';
            $values['VERBOSITY'] = 'MEDIUM';
        } elseif ($this->_mode == 'nvp') {
            $values['STARTDATE'] = $startdate;
            $values['TRANSACTIONID'] = $txnID;
            $values['EMAIL'] = $email;
            if (is_array($options))
                $values = array_merge($values, $options);
        }
        return $this->_request($values, 'TransactionSearch');
    }
    
    /**
     * Set a parameter as passed.
     */
    public function setParam($name, $value) 
    {
        $name = '_'.$name;
        $this->$name = $value;
    }
    
    /**
     * Set cURL options.
     */
    public function setCurlOption($name, $value) 
    {
        $this->_curlOptions[$name] = $value;
    }
    
    /**
     * Send a request to endpoint.
     */
    protected function _request($values, $operation, $requestId = null) 
    {
        /*if (isset($values['in']))
         unset($values['in']);*/
        if ($this->_mode == 'nvp') {
            $values['METHOD'] = $operation;
        }
        if ($this->_mode == 'payflow') {
            $values['REQUEST_ID'] = time();
        }
        // convert currency code to proper key name for nvp
        if ($this->_mode == 'nvp') {
            if (!isset($values['CURRENCYCODE']) && isset($values['CURRENCY'])) {
                $values['CURRENCYCODE'] = $values['CURRENCY'];
                unset($values['CURRENCY']);
            }
        }
        // request-id must be unique within 30 days
        if (null === $requestId) {
            $requestId = md5(uniqid(mt_rand()));
        }
        
        $headers[] = 'Content-Type: text/namevalue';
        $headers[] = 'X-VPS-Timeout: 45';
        $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";
        if ($this->_mode == 'payflow') {
            $headers[] = 'X-VPS-VIT-Integration-Product: PHP::Axis - Payflow Pro';
        } elseif ($this->_mode == 'nvp') {
            $headers[] = 'X-VPS-VIT-Integration-Product: PHP::Axis - WPP-NVP';
        }
        $headers[] = 'X-VPS-VIT-Integration-Version: 1.3.8a';
        $this->lastHeaders = $headers;
        
        $ch = curl_init();
        //
        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        curl_setopt($ch, CURLOPT_URL, $this->_endpoints[$this->_server]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_buildNameValueList($values));
        foreach ($this->_curlOptions as $name => $value) {
            curl_setopt($ch, $name, $value);
        }
        
        $response = curl_exec($ch);
        $commError = curl_error($ch);
        $commErrNo = curl_errno($ch);
        
        $commInfo = @curl_getinfo($ch);
        curl_close($ch);
        
        $rawdata = "CURL raw data:\n" . $response . "CURL RESULTS: (" . $commErrNo.') ' . $commError . "\n" . print_r($commInfo, true)."\nEOF";
        $errors = ($commErrNo != 0 ? "\n(".$commErrNo.') '.$commError : '');
        $response .= '&CURL_ERRORS='.($commErrNo != 0 ? urlencode('('.$commErrNo.') '.$commError) : '');
        $response .= ($commErrNo != 0 ? '&CURL_INFO='.urlencode($commInfo) : '');
        if ($response) {
            $response = $this->_parseNameValueList($response);
        } else {
            return false;
        }
        $response['in'] = $values;
        return $response;
    }
    
    /**
     * Take an array of name-value pairs and return a properly
     * formatted list. Enforces the following rules:
     *
     *   - Names must be uppercase, all characters must match [A-Z].
     *   - Values cannot contain quotes.
     *   - If values contain & or =, the name has the length appended to
     *     it in brackets (NAME[4] for a 4-character value.
     *
     * If any of the "cannot" conditions are violated the function
     * returns false, and the caller must abort and not proceed with
     * the transaction.
     */
    protected function _buildNameValueList($pairs) 
    {
        // Add the parameters that are always sent.
        $commpairs = array();
        // generic:
        if ($this->_user != '')
            $commpairs['USER'] = str_replace('+', '%2B', trim($this->_user));
        if ($this->_pwd != '')
            $commpairs['PWD'] = trim($this->_pwd);
        // PRO2.0 options:
        if ($this->_partner != '')
            $commpairs['PARTNER'] = trim($this->_partner);
        if ($this->_vendor != '')
            $commpairs['VENDOR'] = trim($this->_vendor);
        // NVP-specific options:
        if ($this->_version != '')
            $commpairs['VERSION'] = trim($this->_version);
        if ($this->_signature != '')
            $commpairs['SIGNATURE'] = trim($this->_signature);
        $pairs = array_merge($pairs, $commpairs);
        
        //if (PAYPAL_DEV_MODE == 'true') $this->log('_buildNameValueList - breakpoint 1 - pairs+commpairs: ' . print_r($pairs, true));
        
        $string = array();
        foreach ($pairs as $name=>$value) {
            if (preg_match('/[^A-Z_0-9]/', $name)) {
                //if (PAYPAL_DEV_MODE == 'true') $this->log('_buildNameValueList - datacheck - ABORTING - preg_match found invalid submission key: ' . $name . ' (' . $value . ')');
                return false;
            }
            // remove quotation marks
            $value = str_replace('"', '', $value);
            // if the value contains a & or = symbol, handle it differently
            if (($this->_mode == 'payflow') && (strpos($value, '&') !== false || strpos($value, '=') !== false)) {
                $string[] = $name.'['.strlen($value).']='.$value;
                //if (PAYPAL_DEV_MODE == 'true') $this->log('_buildNameValueList - datacheck - adding braces and string count to: ' . $value . ' (' . $name . ')');
            } else {
                if ($this->_mode == 'nvp' && ((strstr($name, 'SHIPTO') || strstr($name, 'L_NAME')) && (strpos($value, '&') !== false || strpos($value, '=') !== false)))
                    $value = urlencode($value);
                $string[] = $name.'='.$value;
            }
        }
        
        $this->lastParamList = implode('&', $string);
        return $this->lastParamList;
    }
    
    /**
     * Take a name/value response string and parse it into an
     * associative array. Doesn't handle length tags in the response
     * as they should not be present.
     */
    protected function _parseNameValueList($string) 
    {
        $string = str_replace('&amp;', '|', $string);
        $pairs = explode('&', str_replace(array("\r\n", "\n"), '', $string));
        //$this->log('['.$string . "]\n\n[" . print_r($pairs, true) .']');
        $values = array();
        foreach ($pairs as $pair) {
            list($name, $value) = explode('=', $pair, 2);
            $values[$name] = str_replace('|', '&amp;', $value);
        }
        return $values;
    }
}