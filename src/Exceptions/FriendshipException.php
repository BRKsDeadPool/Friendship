<?php

namespace BRKsDeadPool\Friendship\Exceptions;

use Exception;
use Throwable;

class FriendshipException extends Exception
{

    public function __construct($message = 'An error ocurred', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}