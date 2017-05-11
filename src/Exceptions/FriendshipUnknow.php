<?php

namespace BRKsDeadPool\Friendship\Exceptions;

use BRKsDeadPool\Friendship\Exceptions\FriendshipException;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class FriendshipUnknow extends FriendshipException {
    protected $user;

    protected $friend;

    protected $message = "Already friend of model";

    public function __construct($message = "", $code = 0, Throwable $previous = null, Model $user, Model $friend)
    {
        parent::__construct($message, $code, $previous);

        $this->user = $user->getKey();
        $this->friend = $friend->getKey();
        $this->message = "The user $this->user has no friendship with user $this->friend";
    }
}