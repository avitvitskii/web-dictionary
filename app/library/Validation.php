<?php

namespace Core;

use Phalcon\Validation\ValidatorInterface;

class Validation
{
	/**
	 * Create Phalcon Validation from array.
	 * Array format:
	 * [
	 *      '<col1>' => [
	 *          'Validator1',
	 *          'Validator2' => [<validator options>],
	 *          ...
	 *      ],
	 *      ...
	 * ]
	 *
	 * @param array $validatorsArray
	 * @param array|null $commonOptions
	 * @return \Phalcon\Validation
	 */
	public static function fromArray(array $validatorsArray, array $commonOptions = null) :\Phalcon\Validation
	{
		$validation = new \Phalcon\Validation();

		if ($commonOptions === null) {
			$commonOptions = [];
		}

		foreach ($validatorsArray as $col => $validators) {
			if (!is_array($validators)) {
				$validators = [$validators];
			}
			foreach ($validators as $key => $val) {
				if (is_array($val)) {
					$name = $key;
					$options = $val;
				} else {
					$name = $val;
					$options = [];
				}
				$options += $commonOptions;
				if (class_exists($name)) {
					$validator = new $name($options);
				} else {
					$name = '\Phalcon\Validation\Validator\\' . $name;
					if (class_exists($name)) {
						$validator = new $name($options);
					} else {
						throw new Exception('Unknown validator: \'' . $name . '\'');
					}
				}
				if (!($validator instanceof ValidatorInterface)) {
					throw new Exception(get_class($validator) . ' is not a valid ValidatorInterface instance');
				}
				$validation->add($col, $validator);
			}
		}

		return $validation;
	}
}