<?php
use Phalcon\Di\FactoryDefault;

ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

date_default_timezone_set('Europe/Moscow');

define('PUBLIC_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('APP_ID', sha1(gethostname() . ':' . BASE_PATH));

try {

    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();

    /**
     * Handle routes
     */
    require_once APP_PATH . '/config/router.php';

    /**
     * Read services
     */
    require_once APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    switch ($config->application->environment) {
        case 'develop':
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            break;
        default:
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
    }

    $openDirs = $config->security->openDirs;
    if ($openDirs instanceof \Phalcon\Config) {
        $openDirs = $openDirs->toArray();
    }
    if (!$openDirs) {
        $openDirs = [
            BASE_PATH,
            sys_get_temp_dir()
        ];
    }
    ini_set(
        'open_basedir',
        implode(PATH_SEPARATOR, $openDirs)
    );

    /**
     * Include Autoloader
     */
    require_once APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    echo str_replace(["\n","\r","\t"], '', $application->handle()->getContent());

} catch (\Throwable $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
