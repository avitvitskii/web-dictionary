<?php

namespace Core;

use Core\Cls\Singleton;
use Phalcon\Di;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\View\Simple;

class View
{
    use Singleton;

    /**
     * Instance
     * @var self
     */
    protected static $_instance = null;

    /**
     * View instance to render volt files
     * @var Simple
     */
    protected $_voltView = null;

    /**
     * Get View instance to render volt files
     *
     * @return Simple
     */
    public function voltRenderer() :Simple
    {
        if ($this->_voltView === null) {
            $di = Di::getDefault();
            $config = $di->get('config');

            $this->_voltView = new Simple();

            $this->_voltView->setViewsDir($config->application->viewsDir);
            $this->_voltView->setDI(Di::getDefault());

            $voltCacheDir = $config->application->cacheDir;

            $this->_voltView->registerEngines(['.volt' => function($view, $di) use ($voltCacheDir) {
                $volt = new Volt($view, $di);
                $volt->setOptions(
                    [
                        'compiledPath' => function ($path) use ($voltCacheDir) {
                            return $voltCacheDir . str_replace(['/', '\\'], '_', $path) . '.php';
                        }
                    ]
                );
                return $volt;
            }]);
        }

        return $this->_voltView;
    }

    /**
     * Render volt file to string
     *
     * @param string $volt
     * @param array $vars
     * @return string
     */
    public function renderVolt(string $volt, array $vars = []) :string
    {
        $view = $this->voltRenderer();

        $view->setVars($vars);

        return $view->render($volt);
    }

    /**
     * Render volt file to HTML file
     *
     * @param string $volt
     * @param array $vars
     * @param string|null $target
     * @return string
     */
    public function voltToHtmlFile(string $volt, array $vars = [], string $target = null) :string
    {
        if ($target === null) {
            $di = Di::getDefault();
            /**
             * @var DataStorage $ds
             */
            $ds = $di->get('ds');

            $target = $ds->dir('volt-to-html');
            $target .= sha1($volt . serialize($vars)) . '.html';

            File::registerTemp($target);
        }

        $content = $this->renderVolt($volt, $vars);

        if (file_put_contents($target, $content) === false) {
            throw new Exception('Failed to save HTML file to ' . $target);
        }

        return $target;
    }
}