<?php

/**
 * Implementation of PayPalExpressCheckout
 */
class PayPalExpressGateway extends PayPalGateway {
  /**
   * The PayPal token for this transaction
   *
   * @var String
   */
  private $token = null;

  public function process($data) {

    //parent::process($data);

    $authentication = self::get_authentication();

    $this->postData = array();
    $this->postData['USER'] = $authentication['username'];
    $this->postData['PWD'] = $authentication['password'];
    $this->postData['SIGNATURE'] = $authentication['signature'];

    // $this->postData['VERSION'] = self::PAYPAL_VERSION;
    // $this->postData['VERSION'] = '64';
    $this->postData['VERSION'] = '72.0';

    $this->postData['PAYMENTACTION'] = self::get_action();

    // $this->postData['AMT'] = $data['Amount'];
    // $this->postData['CURRENCY'] = $data['Currency'];
    $this->postData['PAYMENTREQUEST_0_AMT'] = $data['Amount'];
    $this->postData['PAYMENTREQUEST_0_CURRENCYCODE'] = $data['Currency'];

    $this->postData['METHOD'] = 'SetExpressCheckout';

    $this->postData['RETURNURL'] = $this->returnURL;
    $this->postData['CANCELURL'] = $this->returnURL;

    SS_Log::log(new Exception(print_r($this->postData, true)), SS_Log::NOTICE);

    $paystation = new RestfulService(self::get_url(), 0); //REST connection that will expire immediately
		$paystation->httpHeader('Accept: application/xml');
		$paystation->httpHeader('Content-Type: application/x-www-form-urlencoded');
		$response = $paystation->request('', 'POST', http_build_query($this->postData));


    // $endpoint = self::get_url();
    // $data = http_build_query($this->postData);
    // $service = new RestfulService($endpoint);
    // $response = $service->request(null, 'POST', $this->postData);

    //$response = $this->postPaymentData($this->postData);

    SS_Log::log(new Exception(print_r($response, true)), SS_Log::NOTICE);


    if ($response->getStatusCode() != '200') {
      return new PaymentGateway_Failure($response);
    } 
    else {

      if ($token = $this->getToken($response)) {
        // If Authorization successful, redirect to PayPal to complete the payment
        Controller::curr()->redirect(self::get_paypal_redirect_url() . "?cmd=_express-checkout&token=$token");
      } 
      else {
        // Otherwise, return failure message
        $errorList = $this->getErrors($response);
        return new PaymentGateway_Failure(null, null, $errorList);
      }
    }
  }

  /**
   * Get the token value from a valid HTTP response
   *
   * @param SS_HTTPResponse $response
   * @return String|null
   */
  public function getToken($response) {
    $responseArr = $this->parseResponse($response);

    if (isset($responseArr['TOKEN'])) {
      $token = $responseArr['TOKEN'];
      $this->token = $token;
      return $token;
    }

    return null;
  }

  /**
   * @see PaymentGateway_GatewayHosted
   */
  public function getResponse($response) {
    // Get the payer information
    $this->preparePayPalPost();
    $this->postData['METHOD'] = 'GetExpressCheckoutDetails';
    $this->postData['TOKEN'] = $this->token;
    $response = $this->parseResponse($this->postPaymentData($this->data));

    // If successful, complete the payment
    if ($response['ACK'] == self::SUCCESS_CODE || $response['ACK'] == self::SUCCESS_WARNING) {
      $payerID = $response['PAYERID'];

      $this->preparePayPalPost();
      $this->postData['METHOD'] = 'DoExpressCheckoutPayment';
      $this->postData['PAYERID'] = $payerID;
      $this->postData['PAYMENTREQUEST_0_PAYMENTACTION'] = (self::get_action());
      $this->postData['TOKEN'] = ($this->token);
      $response = $this->parseResponse($this->postPaymentData($this->data));

      switch ($responseArr['ACK']) {
        case self::SUCCESS_CODE:
        case self::SUCCESS_WARNING:
          return new PaymentGateway_Result();
          break;
        case self::FAILURE_CODE:
          $errorList = $this->getErrors($response);
          return new PaymentGateway_Failure(null, null, $errorList);
          break;
        default:
          return new PaymentGateway_Failure();
          break;
      }
    }
  }
}

/**
 * Gateway class to mock up PayPalExpress for testing purpose
 */
class PayPalExpressGateway_Mock extends PayPalExpressGateway {

  /* Response template strings */
  private $tokenResponseTemplate = 'TIMESTAMP=&CORRELATIONID=&TOKEN=&VERSION=BUILD=';
  private $failureResponseTemplate = 'ACK=Failure&L_ERRORCODE0=&L_SHORTMESSAGE0=&L_LONGMESSAGE0=';

  /**
   * Generate a mock token response based on the template
   */
  public function generateDummyTokenResponse() {
    $tokenResponseArr = $this->parseResponse($this->tokenResponseTemplate);

    $tokenResponseArr['TIMESTAMP'] = time();
    $tokenResponseArr['CORRELATIONID'] = 'cfcb59afaabb4';
    $tokenResponseArr['TOKEN'] = '2d6TB68159J8219744P';
    $tokenResponseArr['VERSION'] = self::PAYPAL_VERSION;
    $tokenResponseArr['BUILD'] = '1195961';

    return http_build_query($tokenResponseArr);
  }

  /**
   * Generate a mock failure response based on the template
   */
  public function generateDummyFailureResponse() {
    $failureResponseArr = $this->parseResponse($this->failureResponseTemplate);

    $failureResponseArr['L_ERRORCODE0'] = '81002';
    $failureResponseArr['L_SHORTMESSAGE0'] = 'Undefined Method';
    $failureResponseArr['L_LONGMESSAGE0'] = 'Method specified is not supported';

    return http_build_query($failureResponseArr);
  }
}