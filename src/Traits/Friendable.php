<?php

namespace BRKsDeadPool\Friendship\Traits;

use BRKsDeadPool\Friendship\Codes\Status;
use BRKsDeadPool\Friendship\Events\FriendshipSent;
use BRKsDeadPool\Friendship\Models\Friendship;
use Illuminate\Database\Eloquent\Model;

trait Friendable
{
    /**
     * @param $recipient \Illuminate\Database\Eloquent\Model
     * @return \BRKsDeadPool\Friendship\Models\Friendship|bool
     */
    public function beFriend(Model $recipient)
    {
        if (!$this->canBeFriend($recipient)) {
            return false;
        }

        $friendship = (new Friendship)->fillRecipient($recipient)
            ->fillStatus(Status::PENDING);

        $this->friendsFrom()->save($friendship);

        if (function_exists('event')) {
            event(new FriendshipSent($this, $recipient));
        }

        return $friendship;
    }

    /**
     * @param $recipient \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function canBeFriend(Model $recipient){
        return true;
    }

    private function findFriendship(Model $recipient) {
        return  Friendship::betweenModels($this, $recipient);
    }

    /**
     * @param $status int;
     * @return Friendship;
     */
    private function findFriendships($status = null) {
        $query = Friendship::where(function($query) {
           $query->where(function ($q) {
               $q->whereSender($this);
           })->orWhere(function($q) {
               $q->whereRecipient($this);
           });
        });

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function friendsFrom()
    {
        return $this->morphMany(Friendship::class, 'sender');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function friendsOf()
    {
        return $this->morphMany(Friendship::class, 'recipient');
    }
}