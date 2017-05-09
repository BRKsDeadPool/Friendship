<?php

namespace BRKsDeadPool\Friendship\Interfaces;

interface Friendable
{
    public function acceptFriend(); // Status::FRIEND

    public function denyFriend(); // Status::REJECTED

    public function unfriend(); // Status::STRANGER

    public function resetFriend(); // Status::STRANGER

    public function blockFriend(); // Status::BLOCKED

    public function unblockFriend(); // Status::PENDING

    public function favoriteFriend(); // Status::FAVORITE

    public function unfavoriteFriend(); // Status::FRIEND

    public function hasBlocked(); // check if user has blocked friend

    public function isBlockedBy(); // check if user is blocked by friend

    public function hasPendingWith(); // Check if friend has a pending friendship with user

    public function isPendingWith(); // Check if there is a pending friendship with friend

    public function hasFavorited(); // Check if user has favorited a friend

    public function isFavorited(); // Check if user is favorited by a friend

    public function isStrangerWith(); // Check if there is a friendship between the users howerver the status

    public function getFriends(); // Get friends by status

    public function getAllFriends(); // Get all friends however status

    public function getFriend(); // Get a friendship with user

    public function canBeFriend(); // Check if two users can be friends

    public function isSender(); // Check if user is sender from a friendship

    public function isRecipient(); // Check if user is a recipient from a friendship
}