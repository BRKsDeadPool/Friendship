<?php

namespace BRKsDeadPool\Friendship\Support;

use BRKsDeadPool\Friendship\Codes\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use App\User;

trait HasFriends
{

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Action methods
     |------------------------------------------------------------------------------------------------------------------
     | These methods were created to be used inside the package and on the app code, can be used
     | anywhere.
     |
     */

    /**
     * Send friendship request to users
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function beFriend(Model $user): bool
    {
        if (!$this->canBeFriend($user)) return false;

        try {
            $this->friends()->attach($user, [
                'sender_status' => Status::PENDING,
                'recipient_status' => Status::PENDING
            ]);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     *  Accept a friendship request from user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function acceptFriend(Model $user): bool
    {
        // TODO: Implement acceptFriend() method.
    }

    /**
     *  Deny a friendship request from user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function denyFriend(Model $user): bool
    {
        // TODO: Implement denyFriend() method.
    }

    /**
     *  Remove a friend from the friends list
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function removeFriend(Model $user): bool
    {
        // TODO: Implement removeFriend() method.
    }

    /**
     *  Block a friend
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function blockFriend(Model $user): bool
    {
        // TODO: Implement blockFriend() method.
    }

    /**
     *  Unblock a friend
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function unblockFriend(Model $user): bool
    {
        // TODO: Implement unblockFriend() method.
    }

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Getter methods
     |------------------------------------------------------------------------------------------------------------------
     | These methods were created to be used inside the package and on the app code, can be used
     | anywhere.
     |
     */

    /**
     *  Get all mutual friends with a given user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return \Illuminate\Support\Collection
     */
    public function getMutualFriends(Model $user): Collection
    {
        // TODO: Implement getMutualFriends() method.
    }

    /**
     *  Get friends off friends
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFriendsOffFriends(): Collection
    {
        // TODO: Implement getFriendsOffFriends() method.
    }

    /**
     *  Get all friends where status "FRIEND"
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFriends(): Collection
    {
        // TODO: Implement getFriends() method.
    }

    /**
     *  Get all friends however status
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllFriends(): Collection
    {
        // TODO: Implement getAllFriends() method.
    }

    /**
     *  Get all friends where sender_status "PENDING"
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPendingRequests(): Collection
    {
        // TODO: Implement getPendingRequests() method.
    }

    /**
     *  Get all friends where recipient_status "PENDING"
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPendingResponses(): Collection
    {
        // TODO: Implement getPendingResponses() method.
    }

    /**
     *  Get the general status for a friendship with User
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return string
     */
    public function getGeneralStatus(Model $user): string
    {
        // TODO: Implement getGeneralStatus() method.
    }

    /**
     *  Friends where sender
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendships', 'sender', 'recipient')
            ->withPivot('id', 'sender_status', 'recipient_status')->withTimestamps();
    }

    /**
     *  Friends where recipient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function friendsBis(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendships', 'recipient', 'sender')
            ->withPivot('id', 'sender_status', 'recipient_status')->withTimestamps();
    }

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Checker methods
     |------------------------------------------------------------------------------------------------------------------
     | These methods were created to be used inside the package and on the app code, can be used
     | anywhere.
     |
     */

    /**
     *  Check if user is friend of another user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function isFriend(Model $user): bool
    {
        // TODO: Implement isFriend() method.
    }

    /**
     *  Check if has pending response from user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function hasPendingResponseFrom(Model $user): bool
    {
        // TODO: Implement hasPendingResponseFrom() method.
    }

    /**
     *  Check if there is a pending request from user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function hasPendingRequestTo(Model $user): bool
    {
        // TODO: Implement hasPendingRequestTo() method.
    }

    /**
     *  Check if is blocked by user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function isBlockedBy(Model $user): bool
    {
        // TODO: Implement isBlockedBy() method.
    }

    /**
     *  Check if has blocked another user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function hasBlocked(Model $user): bool
    {
        // TODO: Implement hasBlocked() method.
    }

    /**
     *  Check if user can be friend
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function canBeFriend(Model $user): bool
    {
        // TODO: Implement canBeFriend() method.

        return true;
    }

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Query methods
     |------------------------------------------------------------------------------------------------------------------
     | These methods were created to be used only inside the package, should not be used
     | on the app code.
     |
     */

    /**
     *  Query a friendship with a user
     *
     * @param $friend \Illuminate\Database\Eloquent\Model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function queryFriendship(Model $friend)
    {
        return $this->queryFriendships(function ($query) use ($friend) {
            // Sender logic
            $query->where('recipient', $friend->getKey());

        }, function ($query) use ($friend) {
            // Recipient logic
            $query->where('sender', $friend->getKey());

        });
    }

    /**
     *  Query all friendships
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function queryFriendships($senderLogic = null, $recipientLogic = null)
    {
        return $this->whereHas('friends', function ($query) use ($senderLogic, $recipientLogic) {
            $query->where('sender', $this->getKey());

            if (is_callable($senderLogic)) {
                call_user_func($senderLogic, $query);
            }
        })->orWhereHas('friendsBis', function ($query) use ($senderLogic, $recipientLogic) {
            $query->where('recipient', $this->getKey());

            if (is_callable($recipientLogic)) {
                call_user_func($recipientLogic, $query);
            }
        });
    }
}