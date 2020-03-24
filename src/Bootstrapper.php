<?php

namespace Repack\Bladestring;

if (class_exists('\Illuminate\Support\ViewServiceProvider')) {
    class EngineResolver extends \Illuminate\View\Engines\EngineResolver
    {}
    class FileViewFinder extends \Illuminate\View\FileViewFinder
    {}
    class ServiceProvider extends \Illuminate\Support\ViewServiceProvider
    {}
} else {
    class EngineResolver extends \Repack\View\Engines\EngineResolver
    {}
    class FileViewFinder extends \Repack\View\FileViewFinder
    {}
    class ServiceProvider
    {}
}

class Bootstrapper extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // load the alias (handled by the Laravel autoloader)
        //$this->app->alias('StringBlade', __NAMESPACE__.'\Facades\StringBlade');

        $this->registerEngineResolver($ioc);

        Bootstrapper::bootstrap($this->app);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public static function bootstrap($ioc)
    {
        // load the alias (handled by the Laravel autoloader)
        //$ioc->alias('StringBlade',  __NAMESPACE__.'\Facades\StringBlade');

        static::bindEngineResolver($ioc);
        static::bindViewFinder($ioc);
        static::bindFactory($ioc);
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public static function bindFactory($ioc)
    {
        $ioc->singleton('view.string', function () use ($ioc) {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.

            $finder = $ioc->bound('view') ? $ioc['view']->getFinder() : $ioc['view.string.finder'];

            $factory = new Factory(
                $ioc['view.engine.resolver'], $finder, $ioc->bound('events') ? $ioc['events'] : null
            );

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $factory->setContainer($ioc);

            $factory->share('app', $ioc);

            return $factory;
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public static function bindViewFinder($ioc)
    {
        // since view.find should be registered, lets get the paths and hints - in case they have changed
        $oldFinder = array();

        if ($ioc->resolved('view.finder')) {
            $oldFinder['paths'] = $ioc['view']->getFinder()->getPaths();
            $oldFinder['hints'] = $ioc['view']->getFinder()->getHints();
        }

        // recreate the view.finder
        $ioc->bind('view.string.finder', function () use ($oldFinder, $ioc) {

            $paths = (isset($oldFinder['paths']))
            ? array_unique(array_merge($ioc['config']['view.paths'], $oldFinder['paths']), SORT_REGULAR)
            : $ioc['config']['view.paths'];

            if (is_subclass_of(__NAMESPACE__ . '\FileViewFinder', '\Illuminate\View\FileViewFinder')) {
                $viewFinder = new FileViewFinder($ioc['files'], $paths);
            } else {
                $viewFinder = new FileViewFinder($ioc['config']['view.paths'], $paths);
            }

            if (!empty($oldFinder['hints'])) {
                array_walk($oldFinder['hints'], function ($value, $key) use ($viewFinder) {
                    $viewFinder->addNamespace($key, $value);
                });
            }

            return $viewFinder;
        });
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public static function bindEngineResolver($ioc)
    {
        if ($ioc->bound($resolver = 'view.engine.resolver')) {

            $bindCallback = function ($engineResolver) use ($ioc) {
                // Next, we will register the various view engines with the resolver so that the
                // environment will resolve the engines needed for various views based on the
                // extension of view file. We call a method for each of the view's engines.
                foreach (array('file', 'php', 'blade', 'stringblade') as $engine) {

                    $method = 'bind' . ucfirst($engine) . 'Engine';

                    if (method_exists(__NAMESPACE__ . '\Bootstrapper', $method)) {
                        Bootstrapper::{$method}($engineResolver, $ioc);
                    }
                }
            };

            $bindCallback($ioc[$resolver]);
        }
    }

    /**
     * Register the StringBlade engine implementation.
     *
     * @param  \Engines\EngineResolver  $resolver
     * @return void
     */
    public static function bindStringBladeEngine($resolver, $ioc)
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $ioc->singleton('string.blade.compiler', function () use ($ioc) {
            return new CompilerString($ioc['config']['view.compiled']);
        });

        $resolver->register('stringblade', function () use ($ioc) {
            return new CompilerEngine($ioc['string.blade.compiler']);
        });
    }
}