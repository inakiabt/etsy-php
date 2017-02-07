<?php
namespace Etsy;

use Etsy\AbstractClient;
use Etsy\OAuth\Common\Storage\File;
use Etsy\OAuth\Common\Http\Client\ChunkedStreamClient;
use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth1\Service\Etsy;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\ServiceFactory;

/**
*
*/
class Client extends AbstractClient {
	private $oauth = null;

	private $storage;

	function __construct($consumer_key, $consumer_secret, $storageFilePath) {
		parent::__construct($consumer_key, $consumer_secret);

		$this->storage = new File($storageFilePath);
		$credentials = new Credentials(
		    $consumer_key,
		    $consumer_secret,
		    null
		);

		$serviceFactory = new ServiceFactory();
		$serviceFactory->setHttpClient(new ChunkedStreamClient());
		$this->oauth = $serviceFactory->createService('Etsy', $credentials, $this->storage);
	}

	public function request($path, $params = array(), $method = 'GET', $json = true) {
        return json_decode($this->oauth->request($this->base_path . $path, $method, $params), !$json);
	}

	public function getRequestToken(array $extra = array()) {
		$this->oauth->setScopes($extra);
	    $response = $this->oauth->requestRequestToken();
	    $extra = $response->getExtraParams();

	    return array(
	    	'oauth_token' => $response->getRequestToken(),
	    	'oauth_token_secret' => $response->getRequestTokenSecret(),
	    	'login_url' => $extra['login_url']
	    );
	}

	public function getAccessToken($access_token, $verifier) {
		$token = $this->storage->retrieveAccessToken('Etsy');
	    $response = $this->oauth->requestAccessToken(
	        $access_token,
	        $verifier,
	        $token->getRequestTokenSecret()
	    );

	    return array(
	    	'oauth_token' => $access_token,
	    	'oauth_token_secret' => $response->getAccessTokenSecret()
	    );
	}
}