<?php
namespace Etsy;

class RequestValidator
{
	public static function validateParams($args, $methodInfo)
	{
		$result = array('_valid' => array());
		if (!is_array($methodInfo))
		{
			$result['_invalid'][] = 'Method not found';
			return $result;
		}

		$methodsParams = $methodInfo['params'];

		if (preg_match_all('@\/\:(\w+)@', $methodInfo['uri'], $match))
		{
			if (isset($args['params']))
			{
				foreach ($match[0] as $i => $value)
				{
					if (!isset($args['params'][$match[1][$i]]))
					{
						$result['_invalid'][] = 'Required parameter "'.$match[1][$i].'" not found';
					}
				}
			} else {
				$result['_invalid'][] = 'Required parameters not found: ' . implode(', ', $match[1]);
			}

			if (isset($result['_invalid']))
			{
				return $result;
			}
		}

		if (isset($args['data']))
		{
			$dataResult = RequestValidator::validateData($args['data'], $methodInfo);
			return array_merge($result, $dataResult);
		}

		return $result;
	}

	protected static function transformValueType($type) {
		switch($type)
		{
			case 'integer':
				return 'int';
			case 'double':
				return 'float';
		}
		return $type;
	}

	public static function validateData($args, $methodInfo)
	{
		$result = array('_valid' => array());
		if (!is_array($methodInfo))
		{
			$result['_invalid'][] = 'Method not found';
			return $result;
		}
		$methodsParams = $methodInfo['params'];
		foreach ($args as $name => $arg)
		{
			if (isset($methodsParams[$name]))
			{
				$validType = $methodsParams[$name];
				$type = self::transformValueType(gettype($arg));
				switch($type)
				{
					case 'array':
						if (@array_key_exists('json', $arg) && json_decode($arg['json']) !== NULL) {
							$type = 'json';
							$arg = $arg['json'];
							break;
						}

						if (count($arg) > 0)
						{
							if (preg_match('@^map\(@', $validType)) {
								$valueTypes = array();
								foreach ($arg as $value)
								{
									$valueTypes[] = self::transformValueType(gettype($value));
								}
								$type = 'map(' . implode($valueTypes, ', ') . ')';
								break;
							}

							if (preg_match('/@.*?;type=.*?\/.+$/', @$arg[0]))
							{
								$type = 'imagefile';
								$name = '@' . $name;
								$arg = @$arg[0];
							} else {
								$item_type = self::transformValueType(@gettype($arg[0]));
								$type = 'array('.$item_type.')';
							}
						}

                        break;

                    case 'string':
                        if(preg_match("/^[\s0-9,]+$/", $arg)) {   //is comma separated integer string
                            $type = 'array(int)';
                        }
                        break;

				}

                if ($validType !== $type) {
                    if (substr($validType, 0, 4) === 'enum') {
                        if ($arg === 'enum' || !preg_match("@" . preg_quote($arg) . "@", $validType)) {
                            $result['_invalid'][] = 'Invalid enum data param "' . $name . '" value (' . $arg . '): valid values "' . $validType . '"';
                        } else {
                            $result['_valid'][$name] = $arg;
                        }
                    } elseif ($type === 'array' && substr($validType, 0, 5) === 'array' ||
                        $type === 'string' && $validType === 'text'
                    ) {
                        $result['_valid'][$name] = $arg;
                    } elseif ($type === 'json' && substr($validType, 0, 5) === 'array') {
                        $result['_valid'][$name] = $arg;
                    } elseif ($type === 'json' && substr($validType, 0, 6) === 'string') {
                        $result['_valid'][$name] = $arg;
                    } else {
                        $result['_invalid'][] = RequestValidator::invalidParamType($name, $arg, $type, $validType);
                    }
                } else {
                    $result['_valid'][$name] = $arg;
                }
			} else {
				$result['_invalid'][] = RequestValidator::invalidParam($name, $arg, gettype($arg));
			}
		}

		return $result;
	}

	public static function invalidParam($name, $value, $type)
	{
		return 'Unrecognized data param "'.$name.'" ('.$type.')';
	}

	public static function invalidParamType($name, $value, $type, $validType)
	{
		return 'Invalid data param type "'.$name.'" ('.(is_array($value) ? implode(', ', $value) : $value).': '.$type.'): required type "'.$validType.'"';
	}
}
