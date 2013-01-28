<?php

class PayPalGateway extends PaymentGateway_GatewayHosted {
	
	protected $supportedCurrencies = array(
    'NZD' => 'New Zealand Dollar',
    'USD' => 'United States Dollar',
    'GBP' => 'Great British Pound',
    'AUD' => 'Australian Dollar',
    'CAD' => 'Canadian Dollar',
    'CZK' => 'Czech Koruna',
    'DKK' => 'Danish Krone',
    'EUR' => 'Euro',
    'HKD' => 'Hong Kong Dollar',
    'HUF' => 'Hungarian Forint',
    'JPY' => 'Japanese Yen',
    'NOK' => 'Norwegian Krone',
    'PLN' => 'Polish Zloty',
    'SGD' => 'Singapore Dollar',
    'SEK' => 'Swedish Krona',
    'CHF' => 'Swiss Franc',
  );
	
}

class PayPalGateway_Express extends PayPalGateway {

	//PayPal URLs
	protected static $test_API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
	protected static $test_PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";

	protected static $API_Endpoint = "https://api-3t.paypal.com/nvp";
	protected static $PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
	
	// protected static $version = '64';
	protected static $version = '94.0';
	
	//set custom settings
	protected static $customsettings = array(
		//design
		//'HDRIMG' => "http://www.mysite.com/images/logo.jpg", //max size = 750px wide by 90px high, and good to be on secure server
		//'HDRBORDERCOLOR' => 'CCCCCC', //header border
		//'HDRBACKCOLOR' => '00FFFF', //header background
		//'PAYFLOWCOLOR'=> 'AAAAAA' //payflow colour
		//'PAGESTYLE' => //page style set in merchant account settings
		
		'SOLUTIONTYPE' => 'Sole'//require paypal account, or not. Can be or 'Mark' (required) or 'Sole' (not required)
		//'BRANDNAME'  => 'my site name'//override business name in checkout
		//'CUSTOMERSERVICENUMBER' => '0800 1234 5689'//number to call to resolve payment issues
		//'NOSHIPPING' => 1 //disable showing shipping details
	);
	
	public function getConfig() {
		if (!$this->config) {
			$this->config = Config::inst()->get('PayPalGateway', self::get_environment());
		}
		return $this->config;
	}
	
	public function process($data) {
		
		$data['Amount'] = number_format($data['Amount'], 2, '.', '');
		
		SS_Log::log(new Exception(print_r($data, true)), SS_Log::NOTICE);

    $config = $this->getConfig();
    $authentication = $config['authentication'];
    
    SS_Log::log(new Exception(print_r($authentication, true)), SS_Log::NOTICE);
    
    if(!$authentication['username'] || !$authentication['password'] || !$authentication['signature']){
			return new PaymentGateway_Failure(null, array('You are attempting to make a payment without the necessary credentials set'));
		}
		
		$paymenturl = $this->getTokenURL($data['Amount'], $data['Currency'], $data);
    

		SS_Log::log(new Exception(print_r($paymenturl, true)), SS_Log::NOTICE);
		
		
		if ($paymenturl){
			Controller::curr()->redirect($paymenturl);
			return;
		}
		return new PaymentGateway_Failure();
		
		
		
		


  //   $postData = array();
  //   $postData['USER'] = $authentication['username'];
  //   $postData['PWD'] = $authentication['password'];
  //   $postData['SIGNATURE'] = $authentication['signature'];

  //   $postData['VERSION'] = Config::inst()->get('PayPalGateway', 'version');
  //   $postData['PAYMENTACTION'] = Config::inst()->get('PayPalGateway', 'action');

  //   $postData['PAYMENTREQUEST_0_AMT'] = $data['Amount'];
  //   $postData['PAYMENTREQUEST_0_CURRENCYCODE'] = $data['Currency'];

  //   $postData['METHOD'] = 'SetExpressCheckout';

  //   $postData['RETURNURL'] = $this->returnURL;
  //   $postData['CANCELURL'] = $this->returnURL;
    
    
  //   SS_Log::log(new Exception(print_r($postData, true)), SS_Log::NOTICE);
  //   exit('getting to here');

  //   $service = new RestfulService($config['url'], 0); //REST connection that will expire immediately
		// $service->httpHeader('Accept: application/xml');
		// $service->httpHeader('Content-Type: application/x-www-form-urlencoded');
		// $response = $service->request('', 'POST', http_build_query($postData));

  //   if ($response->getStatusCode() != '200') {
  //     return new PaymentGateway_Failure($response);
  //   } 
  //   else {

  //     if ($token = $this->getToken($response)) {
  //       // If Authorization successful, redirect to PayPal to complete the payment
  //       Controller::curr()->redirect($config['redirect_url'] . "?cmd=_express-checkout&token=$token");
  //     } 
  //     else {
  //       // Otherwise, return failure message
  //       $errorList = $this->getErrors($response);
  //       return new PaymentGateway_Failure($response, $errorList);
  //     }
  //   }
  }
  
  
  /**
	 * Requests a Token url, based on the provided Name-Value-Pair fields
	 * See docs for more detail on these fields:
	 * https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_SetExpressCheckout
	 * 
	 * Note: some of these values will override the paypal merchant account settings.
	 * Note: not all fields are listed here.
	 */	
	protected function getTokenURL($paymentAmount, $currencyCodeType, $extradata = array()) {

		$data = array(
			//payment info
			'PAYMENTREQUEST_0_AMT' => $paymentAmount,
			'PAYMENTREQUEST_0_CURRENCYCODE' => $currencyCodeType, //TODO: check to be sure all currency codes match the SS ones

			//TODO: include individual costs: shipping, shipping discount, insurance, handling, tax??
			//'PAYMENTREQUEST_0_ITEMAMT' => //item(s)
			//'PAYMENTREQUEST_0_SHIPPINGAMT' //shipping
			//'PAYMENTREQUEST_0_SHIPDISCAMT' //shipping discount
			//'PAYMENTREQUEST_0_HANDLINGAMT' //handling
			//'PAYMENTREQUEST_0_TAXAMT' //tax
			
			//'PAYMENTREQUEST_0_INVNUM' => $this->PaidObjectID //invoice number
			//'PAYMENTREQUEST_0_TRANSACTIONID' => $this->ID //Transactino id
			//'PAYMENTREQUEST_0_DESC' => //description
			//'PAYMENTREQUEST_0_NOTETEXT' => //note to merchant
			
			//'PAYMENTREQUEST_0_PAYMENTACTION' => , //Sale, Order, or Authorization
			//'PAYMENTREQUEST_0_PAYMENTREQUESTID'
			
			//return urls
			'RETURNURL' => $this->returnURL,
			'CANCELURL' => $this->returnURL,
			//'PAYMENTREQUEST_0_NOTIFYURL' => //Instant payment notification
			 
			//'CALLBACK'
			//'CALLBACKTIMEOUT'
						
			//shipping display
			//'REQCONFIRMSHIPPING' //require that paypal account address be confirmed
			'NOSHIPPING' => 1, //show shipping fields, or not 0 = show shipping, 1 = don't show shipping, 2 = use account address, if none passed
			//'ALLOWOVERRIDE' //display only the provided address, not the one stored in paypal
			
			//TODO: Probably overkill, but you can even include the prices,qty,weight,tax etc for individual sale items
					
			//other settings
			//'LOCALECODE' => //locale, or default to US
			'LANDINGPAGE' => 'Billing' //can be 'Billing' or 'Login'

		);

		SS_Log::log(new Exception(print_r($data, true)), SS_Log::NOTICE);
		
		if(!isset($extradata['Name'])){
			$arr =  array();
			if(isset($extradata['FirstName'])) $arr[] = $extradata['FirstName'];
			if(isset($extradata['MiddleName'])) $arr[] = $extradata['MiddleName'];
			if(isset($extradata['Surname'])) $arr[] = $extradata['Surname'];
			
			$extradata['Name'] = implode(' ',$arr);
		}
			
		
		//add member & shipping fields ...this will pre-populate the paypal login / create account form
		foreach(array(
			'Email' => 'EMAIL',
			'Name' => 'PAYMENTREQUEST_0_SHIPTONAME',
			'Address' => 'PAYMENTREQUEST_0_SHIPTOSTREET',
			'AddressLine2' => 'PAYMENTREQUEST_0_SHIPTOSTREET2',
			'City' => 'PAYMENTREQUEST_0_SHIPTOCITY',
			'State' => 'PAYMENTREQUEST_0_SHIPTOSTATE',
			'PostalCode' => 'PAYMENTREQUEST_0_SHIPTOZIP',
			'HomePhone' => 'PAYMENTREQUEST_0_SHIPTOPHONENUM',
			'Country' => 'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'
		) as $field => $val){
			if(isset($extradata[$field])){
				$data[$val] = $extradata[$field];
			}			
		}
		
		//set design settings
		$data = array_merge(self::$customsettings, $data);

		SS_Log::log(new Exception(print_r($data, true)), SS_Log::NOTICE);

		$response = $this->apiCall('SetExpressCheckout', $data);
		
		SS_Log::log(new Exception(print_r($response, true)), SS_Log::NOTICE);
		
		if(!isset($response['ACK']) ||  !(strtoupper($response['ACK']) == "SUCCESS" || strtoupper($response['ACK']) == "SUCCESSWITHWARNING")){
			return null;
		}
		
		//get and save token for later
		$token = $response['TOKEN'];
		// $this->Token = $token;
		// $this->write();

		return $this->getPayPalURL($token);
	}
	
	/**
	 * Handles actual communication with API server.
	 */
	protected function apiCall($method, $data = array()){
		
		$config = $this->getConfig();
    $authentication = $config['authentication'];

		$postfields = array(
			'METHOD' => $method,
			'VERSION' => self::$version,
			'USER' => $authentication['username'],			
			'PWD'=> $authentication['password'],
			'SIGNATURE' => $authentication['signature'],
			'BUTTONSOURCE' => null
		);
		
		$postfields = array_merge($postfields, $data);
		
		//Make POST request to Paystation via RESTful service
		$paystation = new RestfulService($this->getApiEndpoint(), 0); //REST connection that will expire immediately
		$paystation->httpHeader('Accept: application/xml');
		$paystation->httpHeader('Content-Type: application/x-www-form-urlencoded');
		
		$response = $paystation->request('','POST',http_build_query($postfields));	
		
		return $this->deformatNVP($response->getBody());
	}
	
	protected function getApiEndpoint(){
		return (self::get_environment() == 'dev') ? self::$test_API_Endpoint : self::$API_Endpoint;
	}
	
	protected function getPayPalURL($token){
		$url = (self::get_environment() == 'dev') ? self::$test_PAYPAL_URL : self::$PAYPAL_URL;
		return $url.$token.'&useraction=commit'; //useraction=commit ensures the payment is confirmed on PayPal, and not on a merchant confirm page.
	}
	
	protected function deformatNVP($nvpstr){
		$intial = 0;
	 	$nvpArray = array();

		while(strlen($nvpstr)){
			//postion of Key
			$keypos= strpos($nvpstr,'=');
			//position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	     }
		return $nvpArray;
	}
		
}



/**
 * Default class for the common API of PayPal Payment pro
 */
// class PayPalGateway_OLD extends PaymentGateway_GatewayHosted {

 // protected $supportedCurrencies = array(
 //    'NZD' => 'New Zealand Dollar',
 //    'USD' => 'United States Dollar',
 //    'GBP' => 'Great British Pound',
 //    'AUD' => 'Australian Dollar',
 //    'CAD' => 'Canadian Dollar',
 //    'CZK' => 'Czech Koruna',
 //    'DKK' => 'Danish Krone',
 //    'EUR' => 'Euro',
 //    'HKD' => 'Hong Kong Dollar',
 //    'HUF' => 'Hungarian Forint',
 //    'JPY' => 'Japanese Yen',
 //    'NOK' => 'Norwegian Krone',
 //    'PLN' => 'Polish Zloty',
 //    'SGD' => 'Singapore Dollar',
 //    'SEK' => 'Swedish Krona',
 //    'CHF' => 'Swiss Franc',
 //  );

//   /**
//   * Return an array of errors and their messages from a PayPal response
//   *
//   * @param SS_HTTPResponse $response
//   * @return array
//   */
//   public function getErrors($response) {
//     $errorList = array();
//     $responseString = $response->getBody();
//     $responseArr = $this->parseResponse($response);

//     preg_match_all('/L_ERRORCODE\d+/', $responseString, $errorFields);
//     preg_match_all('/L_LONGMESSAGE\d+/', $responseString, $messageFields);

//     if (count($errorFields[0]) != count($messageFields[0])) {
//       throw new Exception("PayPal resonse invalid: errors and messages don't match");
//     } else {
//       for ($i = 0; $i < count($errorFields[0]); $i++) {
//         $errorField = $errorFields[0][$i];
//         $errorCode = $responseArr[$errorField];
//         $messageField = $messageFields[0][$i];
//         $errorMessage = $responseArr[$messageField];
//         $errorList[$errorCode] = $errorMessage;
//       }
//     }

//     return $errorList;
//   }

//   /**
//    * Parse the raw data and response from gateway
//    *
//    * @param $response This can be the response string itself or the
//    *        string encapsulated in a HTTPResponse object
//    * @return array
//    */
//   public function parseResponse($response) {
//     if ($response instanceof RestfulService_Response) {
//       parse_str($response->getBody(), $responseArr);
//     } else {
//       parse_str($response, $responseArr);
//     }

//     return $responseArr;
//   }
// }

// class PayPalGateway_Express_OLD extends PayPalGateway {

//   /**
//    * The PayPal token for this transaction
//    *
//    * @var String
//    */
//   private $token = null;

//   public function process($data) {

//     $config = $this->getConfig();
//     $authentication = $config['authentication'];

//     $postData = array();
//     $postData['USER'] = $authentication['username'];
//     $postData['PWD'] = $authentication['password'];
//     $postData['SIGNATURE'] = $authentication['signature'];

//     $postData['VERSION'] = Config::inst()->get('PayPalGateway', 'version');
//     $postData['PAYMENTACTION'] = Config::inst()->get('PayPalGateway', 'action');

//     $postData['PAYMENTREQUEST_0_AMT'] = $data['Amount'];
//     $postData['PAYMENTREQUEST_0_CURRENCYCODE'] = $data['Currency'];

//     $postData['METHOD'] = 'SetExpressCheckout';

//     $postData['RETURNURL'] = $this->returnURL;
//     $postData['CANCELURL'] = $this->returnURL;
    
    
//     SS_Log::log(new Exception(print_r($postData, true)), SS_Log::NOTICE);
//     exit('getting to here');

//     $service = new RestfulService($config['url'], 0); //REST connection that will expire immediately
// 		$service->httpHeader('Accept: application/xml');
// 		$service->httpHeader('Content-Type: application/x-www-form-urlencoded');
// 		$response = $service->request('', 'POST', http_build_query($postData));

//     if ($response->getStatusCode() != '200') {
//       return new PaymentGateway_Failure($response);
//     } 
//     else {

//       if ($token = $this->getToken($response)) {
//         // If Authorization successful, redirect to PayPal to complete the payment
//         Controller::curr()->redirect($config['redirect_url'] . "?cmd=_express-checkout&token=$token");
//       } 
//       else {
//         // Otherwise, return failure message
//         $errorList = $this->getErrors($response);
//         return new PaymentGateway_Failure($response, $errorList);
//       }
//     }
//   }

//   /**
//    * Get the token value from a valid HTTP response
//    *
//    * @param SS_HTTPResponse $response
//    * @return String|null
//    */
//   public function getToken($response) {
//     $responseArr = $this->parseResponse($response);

//     if (isset($responseArr['TOKEN'])) {
//       $token = $responseArr['TOKEN'];
//       $this->token = $token;
//       return $token;
//     }

//     return null;
//   }

// }
