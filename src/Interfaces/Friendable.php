<?php

namespace BRKsDeadPool\Friendship\Interfaces;

use BRKsDeadPool\Friendship\Models\Friendship;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface Friendable
{
    /**
     * Send a friendship request
     *
     * @param $user
     * @return bool
     */
    public function beFriend($user): bool;

    /**
     *  Accept a friend and set status 'FRIEND'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function acceptFriendship($user): Friendship;

    /**
     *  Cancel a friendship with some user
     *
     * @param $user \App\User
     * @return bool
     */
    public function cancelFriendship($user): bool;

    /**
     *  Block friend and set status as 'BLOCKED'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function blockFriendship($user): Friendship;

    /**
     *  Unblock friend and set status as 'FRIEND'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function unblockFriendship($user): Friendship;

    /**
     *  Favorite a friend and set status as 'FAVORITE'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function favoriteFriendship($user): Friendship;

    /**
     *  Unfavorite a friend and set status as 'FRIEND'
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function unfavoriteFriendship($user): Friendship;

    /**
     *  Get mutual friends with a user
     *
     * @param $user \App\User
     * @return array
     */
    public function getMutualFriends($user): array;

    /**
     *  Get friends off friends
     *
     * @return array
     */
    public function getFriendsOfFriends(): array;

    /**
     *  Get a friendship between users
     *
     * @param $user \App\User
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function getFriendship($user): Friendship;

    /**
     *  Get all friends where status 'FRIEND'
     *
     * @param $status array|string
     * @return array
     */
    public function getFriends($status): array;

    /**
     *  Get all friends however status
     *
     * @return array
     */
    public function getAllFriends(): array;

    /**
     *  Get all pending requests
     *
     * @return array
     */
    public function getPendingRequests(): array;

    /**
     *  Get all pending responses
     *
     * @return array
     */
    public function getPendingResponses(): array;

    /**
     *  Get all blocked frieends
     *
     * @return array
     */
    public function getBlockedFriends(): array;

    /**
     *  Get all favorite friends
     *
     * @return array
     */
    public function getFavoriteFriends(): array;

    /**
     * Return all common friends
     * @return array
     */
    public function getUnfavoriteFriends(): array;

    /**
     *  Return general status between friends
     * @param $user \App\User
     * @return int
     */
    public function getGeneralStatus($user): int;

    /**
     *  Check if user is friend of another user
     * @param $user \App\User
     * @return bool
     */
    public function isFriendOf($user): bool;

    /**
     *  Check if there is a friendship between users
     *
     * @param $user \App\User
     * @return bool
     */
    public function hasFriendshipWith($user): bool;

    /**
     *  Check if user is blocked by another user
     * @param $user \App\User
     * @return bool
     */
    public function isBlockedBy($user): bool;

    /**
     *  Check if user has blocked another user
     * @param $user \App\User
     * @return bool
     */
    public function hasBlocked($user): bool;

    /**
     * Check if user can be friend of another user
     * @param $user \App\User
     * @return boolean
     */
    public function canBeFriend($user): bool;

    /**
     * Friends where user is the sender relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function friends(): HasMany;

    /**
     * Friends where user is the recipient relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function friendsBis(): HasMany;
}