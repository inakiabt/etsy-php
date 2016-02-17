<?php
namespace Etsy\Mocks;

use Etsy\EtsyClient;

class EtsyClientMock extends EtsyClient
{
	function __construct($consumer_key, $consumer_secret)
	{
	}

	public function request($path, $params = array(), $method = OAUTH_HTTP_METHOD_GET, $json = true)
	{
		return array(
			'path' => $path,
			'data' => $params,
			'method' => $method
		);
	}	
}