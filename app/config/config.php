<?php

defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

$config = new \Phalcon\Config(
    [
        'security' => [
            'openDirs' => [
                BASE_PATH,
                sys_get_temp_dir()
            ]
        ],

        'database' => [
            'adapter' => 'Mysql',
            'host' => 'db',
            'username' => 'anton',
            'password' => 'M0nkey13',
            'dbname' => 'database',
            'charset' => 'utf8',
        ],

        'application' => [
            'environment' => 'develop',

            'appDir' => APP_PATH . '/',
            'controllersDir' => APP_PATH . '/controllers/',
            'modelsDir' => APP_PATH . '/models/',
            'migrationsDir' => APP_PATH . '/migrations/',
            'viewsDir' => APP_PATH . '/views/',
            'pluginsDir' => APP_PATH . '/plugins/',
            'libraryDir' => APP_PATH . '/library/',
            'cacheDir' => BASE_PATH . '/cache/',

            // This allows the baseUri to be understand project paths that are not in the root directory
            // of the webpspace.  This will break if the public/index.php entry point is moved or
            // possibly if the web server rewrite rules are changed. This can also be set to a static path.
            'baseUri' => '/',
        ],

        'session' => [
            'name' => 'PHLCSE',
            'cookie' => []
        ],

        'redis' => [
            'cache' => [
                'uniqueId' => APP_ID . ':cache',
                'host' => 'redis',
                'port' => 6379,
                'persistent' => false,
                'index' => 0,
                'statsKey' => '_PHCR'
            ],
            'session' => [
                'uniqueId' => APP_ID . ':session',
                'host' => 'redis',
                'port' => 6379,
                'index' => 1,
                'persistent' => false,
            ],
            'metadata' => [
                'uniqueId' => APP_ID . ':meta',
                'host' => 'redis',
                'port' => 6379,
                'persistent' => false,
                'index' => 2,
                'statsKey' => '_PHCM_MM',
                'lifetime' => 3600 * 24 * 7
            ]
        ],
    ]
);

$extendPath = __DIR__ . '/extend/';
if (!is_dir($extendPath)) {
    return $config;
}

$extend = new DirectoryIterator($extendPath);
foreach ($extend as $item) {
    if ($item->isDot() || $item->isDir() || $item->getExtension() !== 'php') {
        continue;
    }
    $itemConf = include_once $item->getRealPath();
    if ($itemConf instanceof \Phalcon\Config) {
        $config->merge($itemConf);
    }
}

return $config;