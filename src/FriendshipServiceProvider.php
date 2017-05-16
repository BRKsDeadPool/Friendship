<?php

namespace BRKsDeadPool\Friendship;

use Illuminate\Support\ServiceProvider;

class FriendshipServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/friendships.php' => config_path('friendships.php')
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    public function register()
    {

    }
}