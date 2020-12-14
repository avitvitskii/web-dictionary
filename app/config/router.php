<?php

/**
 * @var \Phalcon\Mvc\Router $router
 */
$router = $di->getRouter();
$router->add("/:params", array(
    'controller' => 'index',
    'action' => 'index',
    'params' => 1
));
$router->handle();
