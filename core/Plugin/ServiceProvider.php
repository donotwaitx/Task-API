<?php

namespace MyCore\Plugin;

use MyCore\Actions\ActionHandler;
use MyCore\Actions\ModelAction\ModelActions;
use MyCore\Providers\AbstractModuleProvider;

class ServiceProvider extends AbstractModuleProvider
{
    protected $middleware = [];
    protected $routePrefix = '/';

    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    public function boot()
    {

        // get model actions from plugins
        $pluginActions = [];
        $pluginActions[] = include(base_path('plugins/User/Actions/actions.php'));

        foreach ($pluginActions as $pluginAction) {
            foreach ($pluginAction as $prefix => $config) {
                if (isset($modelActions[$prefix])) {
                    $modelActions[$prefix] = array_merge_recursive($modelActions[$prefix], $config);
                } else {
                    $modelActions[$prefix] = $config;
                }
            }
        }

        // tao action handle --> bindroute
        $actionHandler = new ActionHandler();

        foreach ($modelActions as $prefix => $config) {
            $actionHandler->register($prefix, ModelActions::createActions($prefix, $config),$config);
        }

        $actionHandler->middlewares(['auth:admin'])->bindRoute('/api/action/{prefix}/{action}/{id?}');

    }
}
