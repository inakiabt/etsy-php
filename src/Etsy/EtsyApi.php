<?php
namespace Etsy;

use Etsy\RequestValidator;

/**
* 
*/
class EtsyApi
{
	private $client;
	private $methods = array();
	private $returnJson = false;

	function __construct(EtsyClient $client, $methods_file = null)
	{
		if ($methods_file === null)
		{
			$methods_file = dirname(realpath(__FILE__)) . '/methods.json';
		}

		if (!file_exists($methods_file))
		{
			exit("Etsy methods file '{$methods}' not exists");
		}
		$this->methods = json_decode(file_get_contents($methods_file), true);
		$this->client = $client;
	}

	private function request(array $arguments)
	{
		$method = $this->methods[$arguments['method']];
		$args = $arguments['args'];

		$uri = preg_replace('@:(.+?)(\/|$)@e', '$args["params"]["\\1"]."\\2"', $method['uri']);

		return $this->client->request($uri, @$args['data'], $method['http_method'], $this->returnJson);
	}

	public function getClient()
	{
		return $this->client;
	}

	public function setReturnJson(boolean $returnJson)
	{
		$this->returnJson = $returnJson;
	}

	public function getReturnJson()
	{
		return $this->returnJson;
	}

	/*
	* array('params' => array(), 'data' => array())
	* :params for uri params
	* :data for "post fields"
	*/
	public function __call($method, $args)
	{
		if (isset($this->methods[$method]))
		{
			$validArguments = RequestValidator::validateParams(@$args[0], $this->methods[$method]);
			if (isset($validArguments['_invalid']))
			{
				throw new \Exception('Invalid params for method "'.$method.'": ' . implode(', ', $validArguments['_invalid']) . ' - ' . json_encode($this->methods[$method]));
			}

			return call_user_func_array(array($this, 'request'), array(
																	array(
																		'method' => $method,
																		'args' => array(
																					'data' => @$validArguments['_valid'],
																					'params' => @$args[0]['params']
																					)
																	)));
		} else {
			throw new \Exception('Method "'.$method.'" not exists');
		}
	}
}