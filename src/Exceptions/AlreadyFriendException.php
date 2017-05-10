<?php

namespace BRKsDeadPool\Friendship\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class AlreadyFriendException extends FriendshipException
{
    protected $user;

    protected $friend;

    protected $message = "Already friend of model";

    public function __construct($message = "", $code = 0, Throwable $previous = null, Model $user, Model $friend)
    {
        parent::__construct($message, $code, $previous);

        $this->user = $user->getKey();
        $this->friend = $friend->getKey();
        $this->message = "The user $this->user already has friendship with user $this->friend";
    }
}