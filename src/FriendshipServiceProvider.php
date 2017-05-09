<?php

namespace BRKsDeadPool\Friendship;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\ServiceProviderContract;

class FriendshipServiceProvider extends ServiceProvider implements ServiceProviderContract
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    public function register()
    {
        // TODO: Implement register() method.
    }
}