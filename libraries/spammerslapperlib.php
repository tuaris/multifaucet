<?php
/* 
 * 
 * SpammerSlapper
 * 
 * Software License Agreement (BSD License)
 * 
 * Copyright (c) 2011, The Daniel Morante Company, Inc.
 * All rights reserved.
 * 
 * Redistribution and use of this software in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 * 
 *   Redistributions of source code must retain the above
 *   copyright notice, this list of conditions and the
 *   following disclaimer.
 * 
 *   Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the
 *   following disclaimer in the documentation and/or other
 *   materials provided with the distribution.
 * 
 *   Neither the name of The Daniel Morante Company, Inc. nor the names of its
 *   contributors may be used to endorse or promote products
 *   derived from this software without specific prior
 *   written permission of The Daniel Morante Company, Inc.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
 
define("SPAMMERSLAPPER_API_SERVER", "api.spammerslapper.com");

/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _spammerslapper_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}


/**
 * Submits an HTTP POST to a SpammerSlapper server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function _spammerslapper_http_post($host, $path, $data, $port = 80) {

        $req = _spammerslapper_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: SpammerSlapper/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}

/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return SpammerSlapperResult
  */
function spammerslapper_check ($key, $formdata = array(), $options = array()) {
	if ($key == null || $key == '') {
		die ("To use SpammerSlapper you must get an API key from <a href='http://api.spammerslapper.com/'>http://api.spammerslapper.com</a>");
	}
	
	//Build Query Object
  $query = new SpammerSlapperQuery();
  
  //Automaticly get the IP Address (it can be overrided below if passed via a data array)
  $query->IP_ADDRESS = $_SERVER["REMOTE_ADDR"];
  
  //Set Data (if Any)
  if(!empty($formdata)){ 
    if(isset($formdata['remoteAddress'])){$query->IP_ADDRESS = $formdata['remoteAddress'];}
    if(isset($formdata['emailAddress'])){$query->EMAIL_ADDRESS = $formdata['emailAddress'];}
    if(isset($formdata['message'])){$query->MESSAGE = $formdata['message'];}
    if(isset($formdata['subject'])){$query->SUBJECT = $formdata['subject'];}
    if(isset($formdata['username'])){$query->USERNAME = $formdata['username'];}
  }
  
  //Set Options (if any)
  if(!empty($options)){
    if(isset($options['CHECK_SPAMASSASIN'])){$query->CHECK_SPAMASSASIN = $options['CHECK_SPAMASSASIN'];}
    if(isset($options['CHECK_PROXY'])){$query->CHECK_PROXY = $options['CHECK_PROXY'];}
    if(isset($options['CHECK_HTTPBL'])){$query->CHECK_HTTPBL = $options['CHECK_HTTPBL'];}
    if(isset($options['CHECK_FORUMSPAM'])){$query->CHECK_FORUMSPAM = $options['CHECK_FORUMSPAM'];}
    if(isset($options['CHECK_EMAIL'])){$query->CHECK_EMAIL = $options['CHECK_EMAIL'];}
    if(isset($options['CHECK_DBLORG'])){$query->CHECK_DBLORG = $options['CHECK_DBLORG'];}
    if(isset($options['CHECK_SPAMHAUSDBL'])){$query->CHECK_SPAMHAUSDBL = $options['CHECK_SPAMHAUSDBL'];}
  }
  
  //Package it Up
  $package = base64_encode(json_encode($query));

  //Send the Package
  $response = _spammerslapper_http_post (SPAMMERSLAPPER_API_SERVER, "/check.php",
                                    array (
                                           'key' => $key,                                               
                                           'package' => $package
                                           )
                                    );

  //Result!
  $answers = explode ("\n", $response [1]);
  return new SpammerSlapperResult(json_decode($answers[0]));
}


class SpammerSlapperResult{
  public $SPAM;
  public $Message;
  public $Reason;
  public $Details = array();
  public $Tests = array();    
  
  function __construct($result = null){     
    if($result != null){
      $this->SPAM = $result->SPAM;
      $this->Message = $result->Message;
      $this->Reason = $result->Reason;
      $this->Details = $result->Details;
      $this->Tests = $result->Tests;   
    } 
  }
}


class SpammerSlapperQuery{
  public $IP_ADDRESS;
  public $EMAIL_ADDRESS;
  public $MESSAGE ;
  public $SUBJECT;
  public $USERNAME;
  
  public $CHECK_SPAMASSASIN = true;
  public $CHECK_PROXY = false;
  public $CHECK_HTTPBL = true;
  public $CHECK_FORUMSPAM = true;
  public $CHECK_EMAIL = true;     
  public $CHECK_DBLORG = true;     
  public $CHECK_SPAMHAUSDBL = true;     
}
?>