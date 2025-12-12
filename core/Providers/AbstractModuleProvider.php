<?php

namespace MyCore\Providers;

use Illuminate\Support\ServiceProvider;

class AbstractModuleProvider extends ServiceProvider
{
    // protected $namespace = '';
    // protected $featureName = '';
    // protected $dir = '';
    // protected $routePrefix;
    // protected $viewPrefix;
    // protected $middleware = [];
    // /**
    //  * The policy mappings for the application.
    //  *
    //  * @var array
    //  */
    // protected $policies = [
    //     // 'CustomerApp\Models\Model' => 'CustomerApp\Policies\ModelPolicy',
    // ];

    // /**
    //  * Register any authentication / authorization services.
    //  *
    //  * @return void
    //  */
    // public function boot()
    // {
    //     $this->registerPolicies();
    //     $this->mapRoutes();
    // }

    // /**
    //  * Register any application services.
    //  *
    //  * @return void
    //  */
    // public function register()
    // {
    //     $class_info = new \ReflectionClass($this);
    //     $this->dir = dirname($class_info->getFileName());
    //     $dirName = basename(dirname($class_info->getFileName()));
    //     $this->namespace = 'Modules\\' . $dirName;
    //     $this->featureName = strtolower($dirName);
    //     if (!$this->viewPrefix) {
    //         $this->viewPrefix = $dirName;
    //     }
    //     if (is_dir($this->dir . '/resources/views')) {
    //         $this->loadViewsFrom($this->dir . '/resources/views', $this->viewPrefix);

    //     }

    //     if (!$this->routePrefix) {
    //         $this->routePrefix = $this->featureName;
    //     }

    // }

    // /**
    //  * Define the "web" routes for the application.
    //  *
    //  * These routes all receive session state, CSRF protection, etc.
    //  *
    //  * @return void
    //  */
    // protected function mapRoutes()
    // {
    //     if (is_file($this->dir . '/routes.php')) {
    //         Route::prefix($this->routePrefix)
    //             ->group($this->dir . '/routes.php');
    //     }
    // }

}
