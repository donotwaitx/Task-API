<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function bootstrap(): void
    {
        //
    }
}
