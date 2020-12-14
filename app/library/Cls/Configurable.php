<?php

namespace Core\Cls;

use Phalcon\Config;
use Phalcon\Di;

trait Configurable
{
	/**
	 * App config section.
	 * Define this property in your class to load config from appconf-><section_name>
	 * @var string
	 */
	// protected $_appConfigSection = '<section_name>';

	/**
	 * Config instance
	 * @var Config
	 */
	protected $_config = null;

	/**
	 * Set config instance
	 *
	 * @param Config $config
	 * @return $this
	 */
	public function setConfig(Config $config)
	{
        $this->_config = $config;

		return $this;
	}

	/**
	 * Get config instance or value
	 *
	 * @param string|null $key
	 * @param null $default
	 * @return Config|mixed
	 */
	public function config(string $key = null, $default = null)
	{
		if ($this->_config === null) {
			$config = $this->_getDefaultConfig();
			if ($config) {
				if (!($config instanceof Config)) {
					$config = new Config($config);
				}
			} else {
				$config = new Config();
			}
			$this->_config = $config;
		}

		if ($key === null) {
			return $this->_config;
		}

		return $this->_config->get($key, $default);
	}

	/**
	 * Get default config instance
	 *
	 * @return mixed
	 */
	protected function _getDefaultConfig()
	{
		if (!empty($this->_appConfigSection)) {
            /**
             * @var Config $config
             */
			$config = Di::getDefault()->get('config');
			return $config->get($this->_appConfigSection, null);
		}

		return null;
	}
}