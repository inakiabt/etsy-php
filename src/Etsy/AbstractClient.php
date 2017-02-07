<?php
namespace Etsy;

use Etsy\ClientInterface;
use Etsy\OAuth\Common\Storage\File;
use Etsy\OAuth\Common\Http\Client\ChunkedStreamClient;
use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth1\Service\Etsy;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\ServiceFactory;

/**
*
*/
abstract class AbstractClient implements ClientInterface {
	protected $base_url = 'https://openapi.etsy.com/v2';
	protected $base_path = "/private";

	protected $consumer_key = "";
	protected $consumer_secret = "";

	function __construct($consumer_key, $consumer_secret) {
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
	}

	public function getConsumerKey() {
		return $this->consumer_key;
	}

	public function getConsumerSecret() {
		return $this->consumer_secret;
	}
}