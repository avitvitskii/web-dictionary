<?php

namespace Core;

/**
 * Core Json class
 */
class Json
{
	/**
	 * Encode data to JSON string
	 *
	 * @param  mixed $data
	 * @param  bool $prettyPrint (Optional)
	 * @return string
	 */
	public static function encode($data, bool $prettyPrint = false)
	{
		return json_encode($data, ($prettyPrint ? JSON_PRETTY_PRINT : 0));
	}

	/**
	 * Decode JSON string
	 *
	 * @param  string $json
	 * @param  bool $returnArray (Optional)
	 * @param  bool $silent (Optional)
	 * @param  int &$error
	 * @param  string &$errorText
	 * @return mixed|NULL
	 * @throws Exception
	 */
	public static function decode(string $json, bool $returnArray = true, bool $silent = false, &$error = null, &$errorText = null)
	{
		$data = json_decode($json, $returnArray);
		if ($data === null && ($error = json_last_error())) {
			$errorText = json_last_error_msg();
			if (!$silent) {
				throw new Exception('Failed to decode JSON (error ' . $error . '): ' . $errorText);
			}
			return null;
		}

		return $data;
	}
}