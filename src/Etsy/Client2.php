<?php
namespace Etsy;

use Etsy\AbstractClient;
// use Etsy\OAuth\Common\Storage\File;
// use Etsy\OAuth\Common\Http\Client\ChunkedStreamClient;
// use OAuth\Common\Consumer\Credentials;
// use OAuth\OAuth1\Service\Etsy;
// use OAuth\OAuth1\Token\StdOAuth1Token;
// use OAuth\ServiceFactory;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Plugin\Oauth\OauthPlugin;

/**
*
*/
class Client2 extends AbstractClient {
	private $oauth = null;

	private $storage;

	function __construct($consumer_key, $consumer_secret) {
		parent::__construct($consumer_key, $consumer_secret);

		$this->oauth = new GuzzleClient($this->base_url);
		$oauth = new OauthPlugin(array(
		    'consumer_key'    => $this->getConsumerKey(),
		    'consumer_secret' => $this->getConsumerSecret(),
		));
		$this->oauth->addSubscriber($oauth);
	}

	public function authorize($oauth_token, $oauth_token_secret) {
		$oauth = new OauthPlugin(array(
		    'consumer_key'    => $this->getConsumerKey(),
		    'consumer_secret' => $this->getConsumerSecret(),
		    'token'    => $oauth_token,
		    'token_secret' => $oauth_token_secret,
		));
		$this->oauth->addSubscriber($oauth);
	}

	public function request($path, $params = array(), $method = 'GET', $json = true) {
		try {
			$request = $this->oauth->createRequest($method, $this->base_url . $this->base_path . $path, array(), array(), array(
				'debug' => true
			));
			$request->setBody($params);
	        return $request->send()->json();
	    } catch (\Exception $e) {
	    	$header = $e->getResponse()->getHeader('x-error-detail');

	    	$message = 'Unknown error';
	    	if ($header) {
	    		$message = $header->__toString();
	    	}
	    	throw new \Exception($message, 1, $e);
	    }
	}

	public function getRequestToken(array $extra = array()) {
		$request = $this->oauth->post('oauth/request_token');
		if (!empty($extra)) {
			$request->getQuery()->set('scope', implode(' ', $extra));
		}
		$response = $request->send();

		parse_str($response->getBody(), $map);

	    return $map;
	}

	public function getAccessToken($request_token, $verifier) {
		$conf = array(
		    'consumer_key'    => $this->getConsumerKey(),
		    'consumer_secret' => $this->getConsumerSecret(),
		    'token' => $request_token['oauth_token'],
		    'token_secret' => $request_token['oauth_token_secret'],
		    'verifier' => $verifier,
		);
		$oauth = new OauthPlugin($conf);
		$this->oauth->addSubscriber($oauth);

		$response = $this->oauth->post('oauth/access_token')->send();

		parse_str($response->getBody(), $map);

	    return $map;
	}
}