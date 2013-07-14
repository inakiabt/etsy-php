<?php
namespace Etsy;

/**
* 
*/
abstract class EtsyClient
{
	protected $base_url = "http://openapi.etsy.com/v2";
	protected $base_path = "/private";
	protected $oauth = null;
	protected $authorized = false;
	protected $debug = true;

	protected $consumer_key = "";
	protected $consumer_scret = "";
	protected $lastRequestInfo = "";

	function __construct($consumer_key, $consumer_secret)
	{
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
	}

	public function authorize($access_token, $access_token_secret)
	{
		$this->doAuthorize($access_token, $access_token_secret);
		$this->authorized = true;
	}
	
	public function getConsumerKey()
	{
		return $this->consumer_key;
	}

	public function getConsumerSecret()
	{
		return $this->consumer_secret;
	}

	public function getLastRequestInfo()
	{
		return $this->lastRequestInfo;
	}

	public function setDebug($debug)
	{
		$this->debug = $debug;
	}

	abstract public function request($path, $params = array(), $method = 'GET', $json_output = true);
	abstract public function getRequestToken($permissions = 'oob');
	abstract public function getAccessToken($verifier);
	abstract public function doAuthorize($access_token, $access_token_secret);
}