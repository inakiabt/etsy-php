<?php
namespace Etsy;

/**
* 
*/
class EtsyClient
{
	private $base_url = "http://openapi.etsy.com/v2";
	private $base_path = "/private";
	private $oauth = null;
	private $authorized = false;
	private $debug = true;

	private $consumer_key = "";
	private $consumer_scret = "";

	function __construct($consumer_key, $consumer_secret)
	{
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;

		$this->oauth = new \OAuth($consumer_key, $consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
	}

	public function authorize($access_token, $access_token_secret)
	{
		$this->oauth->setToken($access_token, $access_token_secret);
		$this->authorized = true;
	}

	public function request($path, $params = array(), $method = OAUTH_HTTP_METHOD_GET, $json = true)
	{
		if ($this->authorized === false)
		{
			throw new \Exception('Not authorized. Please, authorize this client with $client->authorize($access_token, $access_token_secret)');
		}
	    try {
	    	if ($this->debug === true)
	        {
	        	$this->oauth->enableDebug();
	        }
	        $data = $this->oauth->fetch($this->base_url . $this->base_path . '/' . $path, $params, $method);
	        $response = $this->oauth->getLastResponse();
	        
	        return json_decode($response, !$json);
	    } catch (\OAuthException $e) {
	        throw new EtsyRequestException($e, $this->oauth);
	    }
	}

	public function getRequestToken($permissions = 'oob')
	{
	    try {
			return $this->oauth->getRequestToken($this->base_url . "/oauth/request_token", $permissions);
	    } catch (\OAuthException $e) {
	        throw new EtsyRequestException($e, $this->oauth);
	    }

	    return null;
	}

	public function getAccessToken($verifier)
	{
	    try {
			return $this->oauth->getAccessToken($this->base_url . "/oauth/access_token", null, $verifier);
	    } catch (\OAuthException $e) {
	        throw new EtsyRequestException($e, $this->oauth);
	    }

	    return null;
	}

	public function getConsumerKey()
	{
		return $this->consumer_key;
	}

	public function getConsumerSecret()
	{
		return $this->consumer_secret;
	}

	public function setDebug($debug)
	{
		$this->debug = $debug;
	}
}

/**
* 
*/
class EtsyRequestException extends \Exception
{
	private $lastResponse;
	private $lastResponseInfo;
	private $lastResponseHeaders;
	private $debugInfo;
	private $exception;

	function __construct($exception, $oauth)
	{
		$this->lastResponse = $oauth->getLastResponse();
		$this->lastResponseInfo = $oauth->getLastResponseInfo();
		$this->lastResponseHeaders = $oauth->getLastResponseHeaders();
		$this->debugInfo = $oauth->debugInfo;
		$this->exception = $exception;

		parent::__construct($this->buildMessage(), 1, $exception);
	}

	private function buildMessage()
	{
		return $this->exception->getMessage().": " . 
			print_r($this->lastResponse, true) .
			print_r($this->lastResponseInfo, true) .
			// print_r($this->lastResponseHeaders, true) .
			print_r($this->debugInfo, true);
	}

	public function __toString()
	{
		return __CLASS__ . ": [{$this->code}]: ". $this->buildMessage();
	}
}