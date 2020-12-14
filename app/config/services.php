<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use \Phalcon\Cache\Frontend\Data as CacheFront;
use \Phalcon\Cache\Backend\Redis as CacheBack;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.phtml' => PhpEngine::class

    ]);

    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter(
        $this->getConfig()->redis->metadata->toArray()
    );
});

/**
 * Models cache
 */
$di->setShared('modelsCache', function () {
    $frontCache = new CacheFront(
        [
            'lifetime' => 3600 * 24,
        ]
    );
    $cache = new CacheBack(
        $frontCache,
        $this->getConfig()->redis->cache->toArray()
    );
    return $cache;
});

/**
 * Security
 */
$di->setShared('security', function () {
    $security = new \Phalcon\Security();
    $security->setWorkFactor(10);
    return $security;
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () use ($di) {

    $config = $this->getConfig();

    $session = new SessionAdapter(
        $config->redis->session->toArray()
    );

    if ($config->session->name) {
        session_name($config->session->name);
    }

    $sessionParams = session_get_cookie_params();

    if ($cookieConfig = $config->session->cookie) {
        $cookieConfig = $cookieConfig->toArray();
        if (!isset($cookieConfig['httponly'])) {
            $cookieConfig['httponly'] = true;
        }
        $sessionParams = array_merge($sessionParams, $cookieConfig);
    }

    $sessionParams['lifetime'] = (int)$sessionParams['lifetime'];

    session_set_cookie_params(
        $sessionParams['lifetime'],
        $config->application->baseUri,
        $sessionParams['domain'],
        $sessionParams['secure'],
        $sessionParams['httponly']
    );

    if ($sessionParams['lifetime'] && $sessionParams['lifetime'] > (int)ini_get('session.gc_maxlifetime')) {
        ini_set('session.gc_maxlifetime', $sessionParams['lifetime']);
    }

    $session->start();

    return $session;
});

/**
 * DataStorage
 */
$di->setShared('ds', function () {
    $path = ($this->getConfig()->application->dataDir ?? sys_get_temp_dir());
    return new \Core\DataStorage($path);
});