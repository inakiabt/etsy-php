<?php
namespace Etsy\Client;

use Etsy\EtsyClient;
use Etsy\Exception\RequestException;
/**
* 
*/
class PeclOAuthEtsyClient extends EtsyClient
{
	function __construct($consumer_key, $consumer_secret)
	{
		parent::__construct($consumer_key, $consumer_secret);

		$this->oauth = new \OAuth($consumer_key, $consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
		if (defined(OAUTH_REQENGINE_CURL))
		{
			$this->oauth->setRequestEngine(OAUTH_REQENGINE_CURL);
		} else {
			error_log("Warning: cURL engine not present on OAuth PECL package. Please install it with: sudo apt-get install libcurl4-dev or sudo yum install curl-devel");
		}
	}

	public function request($path, $params = array(), $method = 'GET', $json = true)
	{
		if ($this->authorized === false)
		{
			throw new \Exception('Not authorized. Please, authorize this client with $client->authorize($access_token, $access_token_secret)');
		}
	    try {
	    	if ($this->debug === true)
	        {
	        	$this->oauth->enableDebug();
	        } else {
	        	$this->oauth->disableDebug();
	        }

	        $this->oauth->fetch($this->base_url . $this->base_path . $path, $params, $method);
	        
	        return json_decode($this->oauth->getLastResponse(), !$json);
	    } catch (\OAuthException $e) {
			$message = $this->oauth->getLastResponse();
			if ($this->debug)
			{
				$this->lastRequestInfo = 'Params: ' . print_r($params, true) ."\n".
					'DebugInfo: ' . print_r($this->oauth->debugInfo, true)."\n".
					'Response: ' . print_r($this->oauth->getLastResponseInfo(), true);
			}
			 
	        throw new RequestException($message);
	    }
	}

	public function doAuthorize($access_token, $access_token_secret)
	{
		$this->oauth->setToken($access_token, $access_token_secret);
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

}