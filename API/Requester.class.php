<?php
class Requester {
  private $endpoint = '';
  private $username = '';
  private $password = '';

  private $accessToken = '';
  private $clientToken = '';

  private $isLogged = false;

  public function __construct($endpoint = 'http://127.0.0.1:8080', $username, $password) {
    $this->endpoint = $endpoint;
    $this->username = $username;
    $this->password = $password;
  }

  private function __request($action, $method = 'GET', $body = []) {
    // Init request
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->endpoint.$action);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    // headers
    if ($this->isLogged) {
      $headers = array(
        'Content-Type: application/json',
        'X-API-SCOPE: login',
        'X-API-USERNAME: ' . $this->username,
        'X-API-PASSWORD: ' . $this->password
      );
    } else {
      $headers = array(
        'Content-Type: application/json',
        'X-API-SCOPE: identify',
        'accessToken: ' . $this->accessToken,
        'clientToken: ' . $this->clientToken
      );
    }

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
      'body' => (isset($body['data'])) ? $body['data'] : ''
    ];
  }

  public function logged() {
    return $this->isLogger;
  }

  public function login($body = array()) {
    list($body, $code, $error) = $this->__request("/", 'GET', $body);

    $success = (isset($body['status'])) ? $body['status'] : false;
    $error = (isset($body['error'])) ? $body['error'] : '';
    $data = (isset($body['data'])) ? $body['data'] : '';

    if ($success) {
      $this->accessToken = $data['accessToken'];
      $this->clientToken = $data['clientToken'];
      $this->isLogged = true;
    }
  }
}
