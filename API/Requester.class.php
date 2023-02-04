<?php
class Requester {
  private $endpoint = '';

  public function __construct($token, $endpoint = 'http://127.0.0.1:8080') {
    $this->endpoint = $endpoint;
  }

  private function __request($action, $method = 'GET', $body = [], $accessToken, $clientToken) {
    // Init request
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->endpoint.$action);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    // headers
    $headers = array(
      'Content-Type: application/json',
      'X-API-SCOPE: identify',
      'accessToken: ' . $accessToken,
      'clientToken: ' . $clientToken
    );
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    if (!empty($body))
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
    // execute
    $result = @json_decode(curl_exec($curl), true);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_errno($curl);
    curl_close($curl);

    return [$result, $code, $error];
  }

  public function get($route, $method = 'GET', $body = array()) {
    list($body, $code, $error) = $this->__request($route, $method, $body);
    return (object)[
      'status' => ($code === 200),
      'code' => $code,
      'success' => (isset($body['status'])) ? $body['status'] : false,
      'error' => (isset($body['error'])) ? $body['error'] : '',
      'body' => (isset($body['data'])) ? $body['data'] : {}
    ];
  }
}
