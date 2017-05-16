<?php

namespace BRKsDeadPool\Friendship\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Friendship extends Pivot
{
    /*public function sender()
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\User'), 'sender');
    }

    public function recipient()
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\User'), 'recipient');
    }*/
}