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

	function __construct($client, $methods_file = null)
	{
		if ($methods_file === null)
		{
			$methods_file = dirname(realpath(__FILE__)) . '/methods.json';
		}

		if (!file_exists($methods_file))
		{
			exit("Etsy methods file '{$methods_file}' does not exist!");
		}
		$this->methods = json_decode(file_get_contents($methods_file), true);
		$this->client = $client;
	}

	public function setReturnJson($returnJson)
	{
		$this->returnJson = $returnJson;
	}

	private function request($arguments)
	{
		$method = $this->methods[$arguments['method']];
		$args = $arguments['args'];
		$params = @$this->prepareData($args['params']);

		$uri = preg_replace_callback('@:(.+?)(\/|$)@', function($matches) use ($args) {
			unset($params[$matches[1]]);
			return $args["params"][$matches[1]].$matches[2];
		}, $method['uri']);

		if (!empty($args['associations']))
		{
			$params['includes'] = $this->prepareAssociations($args['associations']);
		}

		if (!empty($args['fields']))
		{
			$params['fields'] = $this->prepareFields($args['fields']);
		}

		if($method === 'GET') {
			if (!empty($params)) {
				$uri .= "?" . http_build_query($params);
			}
		}

		return $this->validateResponse( $args, $this->client->request($uri, $params, $method['http_method'], $this->returnJson) );
	}

	protected function validateResponse($request_args, $response)
	{
		if (!empty($request_args['associations']))
		{
			$results = $this->returnJson ? @$response->results : @$response['results'];
			if (is_array($results))
			{
				foreach ($results as $result)
				{
					$error_messages = array();
					if ($this->returnJson && isset($result->error_messages))
					{
						$error_messages = $result->error_messages;
					} elseif (!$this->returnJson && isset($result['error_messages'])) {
						$error_messages = $result['error_messages'];
					}

					if (!empty($error_messages))
					{
						foreach ($error_messages as $error_message)
						{
							if (preg_match('@^Access denied on association@', $error_message))
							{
								throw new EtsyResponseException('Invalid association: ' . $error_message, $response);
							}
						}
					}
				}
			}
		}
		return $response;
	}

	private function prepareData($data) {
		$result = array();
		foreach ($data as $key => $value) {
			$type = gettype($value);
			if ($type !== 'boolean') {
				$result[$key] = $value;
				continue;
			}

			$result[$key] = $value ? 1 : 0;
		}

		return $result;
	}

	private function prepareAssociations($associations)
	{
		$includes = array();
		foreach ($associations as $key => $value)
		{
			if (is_array($value))
			{
				$includes[] = $this->buildAssociation($key, $value);
			} else {
				$includes[] = $value;
			}
		}

		return implode(',', $includes);
	}

	private function prepareFields($fields)
	{
		return implode(',', $fields);
	}

	private function buildAssociation($assoc, $conf)
	{
		$association = $assoc;
		if (isset($conf['select']))
		{
			$association .= "(".implode(',', $conf['select']).")";
		}
		if (isset($conf['scope']))
		{
			$association .= ':' . $conf['scope'];
		}
		if (isset($conf['limit']))
		{
			$association .= ':' . $conf['limit'];
		}
		if (isset($conf['offset']))
		{
			$association .= ':' . $conf['offset'];
		}
		if (isset($conf['associations']))
		{
			$association .= '/' . $this->prepareAssociations($conf['associations']);
		}

		return $association;
	}

	/*
	* array('params' => array(), 'data' => array())
	* :params for uri params
	* :data for "post fields"
	*/
	public function __call($method, $args) {
		if (isset($this->methods[$method]))
		{
			$first = isset($arg[0]) ? $arg[0] : [];
			$params = isset($first['params']) ? $first['params'] : [];
			$data = isset($first['data']) ? $first['data'] : [];
			$toValidate = array('params' => @array_merge($params, $data));
			$validArguments = RequestValidator::validateParams($toValidate, $this->methods[$method]);
			if (isset($validArguments['_invalid']))
			{
				throw new \Exception('Invalid params for method "'.$method.'": ' . implode(', ', $validArguments['_invalid']) . ' - ' . json_encode($this->methods[$method]));
			}

			return call_user_func_array(array($this, 'request'), array(
				array(
					'method' => $method,
					'args' => array(
						'params' => @$validArguments['_valid'],
						'associations' => @$args[0]['associations'],
						'fields' => @$args[0]['fields']
					)
				)
			));
		} else {
			throw new \Exception('Method "'.$method.'" not exists');
		}
	}

}
