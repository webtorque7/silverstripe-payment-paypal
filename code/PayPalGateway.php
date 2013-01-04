<?php
/**
 * Default class for the common API of PayPal Payment pro
 */
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

  /**
  * Return an array of errors and their messages from a PayPal response
  *
  * @param SS_HTTPResponse $response
  * @return array
  */
  public function getErrors($response) {
    $errorList = array();
    $responseString = $response->getBody();
    $responseArr = $this->parseResponse($response);

    preg_match_all('/L_ERRORCODE\d+/', $responseString, $errorFields);
    preg_match_all('/L_LONGMESSAGE\d+/', $responseString, $messageFields);

    if (count($errorFields[0]) != count($messageFields[0])) {
      throw new Exception("PayPal resonse invalid: errors and messages don't match");
    } else {
      for ($i = 0; $i < count($errorFields[0]); $i++) {
        $errorField = $errorFields[0][$i];
        $errorCode = $responseArr[$errorField];
        $messageField = $messageFields[0][$i];
        $errorMessage = $responseArr[$messageField];
        $errorList[$errorCode] = $errorMessage;
      }
    }

    return $errorList;
  }

  /**
   * Parse the raw data and response from gateway
   *
   * @param $response This can be the response string itself or the
   *        string encapsulated in a HTTPResponse object
   * @return array
   */
  public function parseResponse($response) {
    if ($response instanceof RestfulService_Response) {
      parse_str($response->getBody(), $responseArr);
    } else {
      parse_str($response, $responseArr);
    }

    return $responseArr;
  }
}

class PayPalGateway_Express extends PayPalGateway {

  /**
   * The PayPal token for this transaction
   *
   * @var String
   */
  private $token = null;

  public function process($data) {

    $config = $this->getConfig();
    $authentication = $config['authentication'];

    $postData = array();
    $postData['USER'] = $authentication['username'];
    $postData['PWD'] = $authentication['password'];
    $postData['SIGNATURE'] = $authentication['signature'];

    $postData['VERSION'] = Config::inst()->get('PayPalGateway', 'version');
    $postData['PAYMENTACTION'] = Config::inst()->get('PayPalGateway', 'action');

    $postData['PAYMENTREQUEST_0_AMT'] = $data['Amount'];
    $postData['PAYMENTREQUEST_0_CURRENCYCODE'] = $data['Currency'];

    $postData['METHOD'] = 'SetExpressCheckout';

    $postData['RETURNURL'] = $this->returnURL;
    $postData['CANCELURL'] = $this->returnURL;

    $service = new RestfulService($config['url'], 0); //REST connection that will expire immediately
		$service->httpHeader('Accept: application/xml');
		$service->httpHeader('Content-Type: application/x-www-form-urlencoded');
		$response = $service->request('', 'POST', http_build_query($postData));

    if ($response->getStatusCode() != '200') {
      return new PaymentGateway_Failure($response);
    } 
    else {

      if ($token = $this->getToken($response)) {
        // If Authorization successful, redirect to PayPal to complete the payment
        Controller::curr()->redirect($config['redirect_url'] . "?cmd=_express-checkout&token=$token");
      } 
      else {
        // Otherwise, return failure message
        $errorList = $this->getErrors($response);
        return new PaymentGateway_Failure($response, $errorList);
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

}
