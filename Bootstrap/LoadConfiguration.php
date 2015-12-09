<?php namespace Orchestra\Config\Bootstrap;

use Orchestra\Config\FileLoader;
use Orchestra\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application;

class LoadConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $env    = null;
        $items  = [];
        $loader = new FileLoader(new Filesystem(), $app->configPath());

        // First we will see if we have a cache configuration file. If we do, we'll load
        // the configuration items from that file so that it is very quick. Otherwise
        // we will need to spin through every configuration file and load them all.
        if (file_exists($cached = $app->getCachedConfigPath())) {
            $items = require $cached;

            $env = Arr::get($items, '*::app.env');
        }

        $app->detectEnvironment(function () use ($env) {
            return $env ?: env('APP_ENV', 'production');
        });

        $app->instance('config', $config = (new Repository($loader, $app->environment()))->setFromCache($items));

        date_default_timezone_set($config['app.timezone']);

        mb_internal_encoding('UTF-8');
    }
}
