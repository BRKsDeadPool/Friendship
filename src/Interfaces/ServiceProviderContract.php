<?php

namespace BRKsDeadPool\Friendship\Interfaces;

interface ServiceProviderContract {
    public function boot();

    public function register();
}