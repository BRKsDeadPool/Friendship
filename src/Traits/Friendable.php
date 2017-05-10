<?php

namespace BRKsDeadPool\Friendship\Traits;

use BRKsDeadPool\Friendship\Codes\Status;
use BRKsDeadPool\Friendship\Models\Friendship;
use Illuminate\Database\Eloquent\Model;
use BRKsDeadPool\Friendship\Interfaces\Friendable as TheContract;
use Illuminate\Database\Eloquent\Relations\HasMany;


trait Friendable
{

    /**
     * Send a friendship request
     *
     * @param $user
     * @return bool
     */
    public function beFriend($user): bool
    {
        // TODO: Implement beFriend() method.
        if (!$this->canBeFriend($user)) {
            return false;
        }

        $friendship = (new Friendship)
            ->fillRecipient($user)
            ->fillStatus(Status::PENDING);

        if ($this->friends()->save($friendship)) {
            return true;
        }

        return false;
    }

    /**
     *  Accept a friend and set status 'FRIEND'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function acceptFriendship($user): Friendship
    {
        // TODO: Implement acceptFriendship() method.
    }

    /**
     *  Cancel a friendship with some user
     *
     * @param $user \App\User
     * @return bool
     */
    public function cancelFriendship($user): bool
    {
        // TODO: Implement cancelFriendship() method.
    }

    /**
     *  Block friend and set status as 'BLOCKED'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function blockFriendship($user): Friendship
    {
        // TODO: Implement blockFriendship() method.
    }

    /**
     *  Unblock friend and set status as 'FRIEND'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function unblockFriendship($user): Friendship
    {
        // TODO: Implement unblockFriendship() method.
    }

    /**
     *  Favorite a friend and set status as 'FAVORITE'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function favoriteFriendship($user): Friendship
    {
        // TODO: Implement favoriteFriendship() method.
    }

    /**
     *  Unfavorite a friend and set status as 'FRIEND'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function unfavoriteFriendship($user): Friendship
    {
        // TODO: Implement unfavoriteFriendship() method.
    }

    /**
     *  Get mutual friends with a user
     *
     * @param $user \App\User
     * @return array
     */
    public function getMutualFriends($user): array
    {
        // TODO: Implement getMutualFriends() method.
    }

    /**
     *  Get friends off friends
     *
     * @return array
     */
    public function getFriendsOfFriends(): array
    {
        // TODO: Implement getFriendsOfFriends() method.
    }

    /**
     *  Get a friendship between users
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function getFriendship($user): Friendship
    {
        // TODO: Implement getFriendship() method.
    }

    /**
     *  Get all friends where status 'FRIEND'
     *
     * @param $status array|string
     * @return array
     */
    public function getFriends($status): array
    {
        // TODO: Implement getFriends() method.
    }

    /**
     *  Get all friends however status
     *
     * @return array
     */
    public function getAllFriends(): array
    {
        // TODO: Implement getAllFriends() method.
    }

    /**
     *  Get all pending requests
     *
     * @return array
     */
    public function getPendingRequests(): array
    {
        // TODO: Implement getPendingRequests() method.
    }

    /**
     *  Get all pending responses
     *
     * @return array
     */
    public function getPendingResponses(): array
    {
        // TODO: Implement getPendingResponses() method.
    }

    /**
     *  Get all blocked frieends
     *
     * @return array
     */
    public function getBlockedFriends(): array
    {
        // TODO: Implement getBlockedFriends() method.
    }

    /**
     *  Get all favorite friends
     *
     * @return array
     */
    public function getFavoriteFriends(): array
    {
        // TODO: Implement getFavoriteFriends() method.
    }

    /**
     * Return all common friends
     * @return array
     */
    public function getUnfavoriteFriends(): array
    {
        // TODO: Implement getUnfavoriteFriends() method.
    }

    /**
     *  Check if user is friend of another user
     * @param $user \App\User
     * @return bool
     */
    public function isFriendOf($user): bool
    {

    }

    public function hasFriendshipWith($user): bool
    {
        return Friendship::betweenModels($this, $user)->exists();
    }

    /**
     *  Check if user is blocked by another user
     * @param $user \App\User
     * @return bool
     */
    public function isBlockedBy($user): bool
    {
        // TODO: Implement isBlockedBy() method.
    }

    /**
     *  Check if user has blocked another user
     * @param $user \App\User
     * @return bool
     */
    public function hasBlocked($user): bool
    {
        // TODO: Implement hasBlocked() method.
    }

    /**
     * Check if user can be friend of another user
     * @param $user \App\User
     * @return boolean
     */
    public function canBeFriend($user): bool
    {
        // TODO: Implement canBeFriend() method.
        if ($this->hasFriendshipWith($user)) {
            return false;
        }
        return true;
    }

    /**
     * Friends where user is the sender relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function friends(): HasMany
    {
        return $this->hasMany(Friendship::class, 'sender');
    }

    /**
     * Friends where user is the recipient relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function friendsBis(): HasMany
    {
        return $this->hasMany(Friendship::class, 'recipient');
    }
}
