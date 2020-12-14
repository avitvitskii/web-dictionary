<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir,
        $config->application->pluginsDir
    ]
)->registerNamespaces(
    array(
        'Core' => $config->application->libraryDir,
        'Helpers' => $config->application->helpersDir
    )
)->register();

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
} elseif (file_exists(BASE_PATH . '/composer.json')) {
    throw new Exception('composer.json exists but no vendor dir found');
}