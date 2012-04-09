<?php
/**
 * PHP library for Emome IMSP SMS's HTTP API
 * 
 * $imsp = new Emome_IMSP_SMS("ACCOUNT", "PASSWORD");
 * $response = $imsp->submitSM(
 *   array("msg" => "MESSAGE HERE",
 *         "to_addr" => "MOBILE PHONE NUMBER HERE"
 * ));
 * var_dump($response);
 * 
 * @author Kun-Fu Tseng, http://www.stu.edu.tw
 * @license MIT License
 */
class Emome_IMSP_SMS 
{
  protected $_host = "http://imsp.emome.net:8008/imsp/sms/servlet";
  protected $_parameters = array();

  /**
   * Constructor
   *
   * @param  string $account
   * @param  string $password
   * @return void
   */
  public function __construct($account, $password) {
    $this->_account = $account;
    $this->_password = $password;

    $this->_parameters = array(
      "account"         => $account,
      "password"        => $password,
      "from_addr_type"  => 0,
      "from_addr"       => null,
      "to_addr_type"    => 0,
      "to_addr"         => null,
      "msg_dlv_time"    => 0,
      "msg_expire_time" => 0,
      "msg_type"        => 0,
      "msg_dcs"         => 0,
      "msg_pclid"       => 0,
      "msg_udhi"        => 0,
      "msg"             => null,
      "dest_port"       => 0
    );
  }

  /**
   * Submit the SM via custom parameters.
   *
   * @param  array $params
   * @return array [to_addr, code, message_id, description]
   */
  public function submitSM($params = array()) 
  {
    $params = array_merge($this->_parameters, $params);
    //print_r($params);
    $params["msg"] = $this->convertMessageByMessageType($params["msg"], $params["msg_type"]);
    $params["dest_port"] = $this->convertDestPortByMessageType($params["dest_port"], $params["msg_type"]);
    $params["to_addr"] = $this->convertToAddr($params["to_addr"]);

    $url = "{$this->_host}/SubmitSM";
    $response = $this->makeHttpRequest($url, "POST", $params);
    return $this->_parseResponse($response);
  }

  /**
   * Old API, delegate to submitSM method.
   *
   * @param  string $message
   * @return string $toAddr
   * @since Nov 2010
   */
  public function sendSM($message, $toAddr) {
    return $this->submitSM(
      array("msg" => $message, "to_addr" => $toAddr)
    );
  }

  /**
   * Parse response from CHT Emome IMSP SubmitSM API.
   *
   * @param  array $params
   * @return array [to_addr, code, message_id, description]
   * @since Nov 2010
   */
  protected function _parseResponse($response) 
  {
    $response = preg_replace('/<[a-zA-Z\/][^>]*>/', '', $response);
    $response = preg_replace('/[\r\n]*/', '', $response);
    $x = explode('|', $response);

    $response = array(
      'to_addr' => $x[0],
      'code' => intval($x[1]),
      'message_id' => $x[2],
      'description' => $x[3]
    );
    return $response;
  }

  /**
   * Return one or more mobile phone number concatenate string with comma.
   *
   * @param  array|string $to_addr
   * @return string 
   */
  protected function convertToAddr($to_addr)
  {
    return is_array($to_addr) 
            ? implode(",", $to_addr) 
            : $to_addr;
  }

  /**
   * Convert message by message type code.
   * 0 or 1 => Big5
   * 2 or 3 => UTF-16 and HEX
   *
   * @param  array|string $to_addr
   * @return string 
   */
  protected function convertMessageByMessageType($msg, $msg_type) 
  {
    iconv_set_encoding("input_encoding", "UTF-8");
    iconv_set_encoding("internal_encoding", "UTF-8");

    // 0 or 1 => Big5
    if ($msg_type <= 1) {
      $msg = iconv("UTF-8", "Big5", $msg);
      echo "$msg####";
    } 
    elseif ($msg_type == 2 || $msg_type == 3) {
      // 2 or 3 => UTF-16 and HEX
      $msg = mb_convert_encoding($msg, "UTF-16", "UTF-8");
      $str = "";
      $len = strlen($str);
      for ($i = 0; $i < $len; ++$i) {
        $byte = $str[$i];
        $char = ord($byte);
        $str .= sprintf('%02x', $char);
      }
      $msg = $str;
    }

    return $msg;
  }

  /**
   * Convert destination port to hexadecimal if message type code is 2 or 3.
   *
   * @param  string $dest_port
   * @param  string $msg_type
   * @return string 
   */
  protected function convertDestPortByMessageType($dest_port, $msg_type)
  {
    if ($msg_type == 2 || $msg_type == 3) {
      // e.g. 1234 => 04D2
      return strtoupper(sprintf('%04x', $dest_port));
    }
  }

  /**
   * Make an HTTP request
   *
   * @param string $url
   * @param string $method
   * @param null|array $postfields
   * @return Server API results
   */
  protected function makeHttpRequest($url, $method, $postfields = null) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_USERAGENT, "Emome_IMSP_SMS");
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);

    if ($method == "POST") {
      curl_setopt($curl, CURLOPT_POST, TRUE);
      if (! empty($postfields)) {
        
        $keys = array_map("urlencode", array_keys($postfields));
        $values = array_map("urlencode", array_values($postfields));
        $postfields = array_combine($keys, $values);
        $fields_str = "";
        foreach ($postfields as $key => $value) { 
          $fields_str .= "$key=$value&"; 
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_str);
      }
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    $response = curl_exec($curl);
    curl_close ($curl);

    return $response;
  }

} // end of class