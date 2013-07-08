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

	function __construct($consumer_key, $consumer_secret)
	{
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
	        $data = $this->oauth->fetch($this->base_url . $this->base_path . '/', $path, $params, $method);
	        $response = $this->oauth->getLastResponse();
	        
	        return json_decode($response, !$json);
	    } catch (\OAuthException $e) {
	        throw new EtsyRequestException($e, $this->oauth->getLastResponse(), $this->ouath->getLastResponseInfo());
	    }
	}

	public function getRequestToken($permissions = 'oob')
	{
	    try {
			return $this->oauth->getRequestToken($this->base_url . "/oauth/request_token", $permissions);
	    } catch (\OAuthException $e) {
	        throw new EtsyRequestException($e, $this->oauth->getLastResponse(), $this->oauth->getLastResponseInfo());
	    }

	    return null;
	}

	public function getAccessToken($verifier)
	{
	    try {
			return $this->oauth->getAccessToken($this->base_url . "/oauth/access_token", null, $verifier);
	    } catch (\OAuthException $e) {
	        throw new EtsyRequestException($e, $this->oauth->getLastResponse(), $this->oauth->getLastResponseInfo());
	    }

	    return null;
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

	function __construct($exception, $lastResponse, $lastResponseInfo)
	{
		$this->lastResponse = $lastResponse;
		$this->lastResponseInfo = $this->lastResponseInfo;

		$message = "{$exception->getMessage()}: " . 
			print_r($this->lastResponse, true) .
			print_r($this->lastResponseInfo, true);

		parent::__construct($message, 1, $exception);
	}

	public function __toString()
	{
		return __CLASS__ . ": [{$this->code}]: {$this->message}: " . 
					print_r($this->lastResponse, true) .
					print_r($this->lastResponseInfo, true);
	}
}