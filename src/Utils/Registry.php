<?php

namespace ProductsImporter\Utils;
use Exception;

class Registry
{
	protected static $registry = [];
	
	public static function bind($key, $value)
	{
		static::$registry[$key] = $value;
	}
	
	public static function get($key)
	{
		try {
			if (array_key_exists($key, static::$registry)) {
				return static::$registry[$key];
			}
		} catch (Exception $e) {
			echo $e;
		}
	}
}