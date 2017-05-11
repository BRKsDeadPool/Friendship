<?php

namespace BRKsDeadPool\Friendship\Traits;

use BRKsDeadPool\Friendship\Codes\Status;
use BRKsDeadPool\Friendship\Exceptions\AlreadyFriendException;
use BRKsDeadPool\Friendship\Exceptions\FriendshipException;
use BRKsDeadPool\Friendship\Exceptions\FriendshipUnknow;
use BRKsDeadPool\Friendship\Models\Friendship;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;


trait Friendable
{
    /*
     |----------------------------------------------------------------------------
     |  Action Methods
     |----------------------------------------------------------------------------
     |
     | These methods were created to use on the app code, can be used by any User
     | Model Instance
     |
    */

    /**
     * Send a friendship request
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @throws FriendshipException
     * @throws AlreadyFriendException
     * @return bool
     */
    public function beFriend(Model $user): bool
    {
        $sender = $this->getKey();
        $recipient = $user->getKey();

        if (!$this->canBeFriend($user)) {
            throw new FriendshipException("User $sender cannot be friend of user $recipient");
        }

        if ($this->isFriendOf($user)) {
            throw new AlreadyFriendException("", 0, null, $this, $user);
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
     * @param $user \Illuminate\Database\Eloquent\Model
     * @throws FriendshipUnknow
     * @throws FriendshipException
     * @return \BRKsDeadPool\Friendship\Models\Friendship|bool
     */
    public function acceptFriendship($user)
    {
        if (!$friendship = $this->findFriendship($user)->first()) {
            throw new FriendshipUnknow("", 0, null, $this, $user);
        }
        $sender = $this->getKey();
        $recipient = $user->getKey();
        $friendshipId = $friendship->getKey();

        if (!$this->isRecipient($user, $friendship)) {
            throw new FriendshipException("User with id: \"$sender\" cannot accept friendship with user id: \"$recipient\" on friendship with id: \"$friendshipId\"");
        }

        if ($friendship->fillStatus(Status::FRIEND)
            ->save()
        ) return $friendship;

        return false;
    }

    /**
     *  Cancel a friendship with some user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @throws FriendshipUnknow
     * @return bool
     */
    public function cancelFriendship(Model $user): bool
    {
        if (!$friendship = $this->hasFriendshipWith($user)) {
            throw new FriendshipUnknow("", 0, null, $this, $user);
        }

        return $this->findFriendship($user)->delete();
    }

    /**
     *  Alias for cancel friendship method
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function rejectFriendship(Model $user): bool
    {
        if (!$this->isFriendOf($user)) {
            return $this->cancelFriendship($user);
        }

        return false;
    }

    /**
     *  Block friend and set status as 'BLOCKED'
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @throws FriendshipUnknow
     * @return \BRKsDeadPool\Friendship\Models\Friendship|int
     */
    public function blockFriendship(Model $user)
    {
        if (!$this->hasFriendshipWith($user)) {
            $this->friends()->save((new Friendship)
                ->fillRecipient($user)
                ->fillRecipientStatus(Status::FRIEND)
                ->fillSenderStatus(Status::PENDING));
        }

        $friendship = $this->findFriendship($user)->first();

        $status = $this->findFriendPosition($user, $friendship) . '_status';

        return $friendship
            ->update([
                $status => Status::BLOCKED
            ]);
    }

    /**
     *  Unblock friend and set status as 'FRIEND'
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @throws FriendshipUnknow
     * @return bool
     */
    public function unblockFriendship(Model $user)
    {
        if (!$this->hasFriendshipWith($user)) {
            throw new FriendshipUnknow("", 0, null, $this, $user);
        }

        $friendship = $this->findFriendship($user)->first();
        $status = $this->findFriendPosition($user, $friendship) . '_status';
        $mstatus = $this->findPosition($user, $friendship) . '_status';


        if ($friendship[$mstatus] == Status::PENDING) {
            return $friendship->delete();
        }

        return $friendship->update([
            $status => Status::FRIEND
        ]);
    }

    /**
     *  Favorite a friend and set status as 'FAVORITE'
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @throws FriendshipUnknow
     * @return bool
     */
    public function favoriteFriendship(Model $user)
    {
        if (!$this->hasFriendshipWith($user)) {
            throw new FriendshipUnknow("", 0, null, $this, $user);
        }

        $friendship = $this->findFriendship($user)->first();

        if ($this->hasBlocked($user, $friendship) || $this->isBlockedBy($user, $friendship)) {
            return false;
        }

        $status = $this->findFriendPosition($user, $friendship) . '_status';
        $mstatus = $this->findPosition($user, $friendship) . '_status';


        if ($friendship[$mstatus] == Status::PENDING) {
            return false;
        }

        return $friendship->update([
            $status => Status::FAVORITE
        ]);
    }

    /**
     *  Unfavorite a friend and set status as 'FRIEND'
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @throws FriendshipUnknow
     * @return bool
     */
    public function unfavoriteFriendship(Model $user)
    {
        if (!$this->hasFriendshipWith($user)) {
            throw new FriendshipUnknow("", 0, null, $this, $user);
        }

        $friendship = $this->findFriendship($user)->first();

        if ($this->hasBlocked($user, $friendship) || $this->isBlockedBy($user, $friendship)) {
            return false;
        }

        $status = $this->findFriendPosition($user, $friendship) . '_status';
        $mstatus = $this->findPosition($user, $friendship) . '_status';


        if ($friendship[$mstatus] == Status::PENDING) {
            return false;
        }

        return $friendship->update([
            $status => Status::FRIEND
        ]);
    }

    /*
     |----------------------------------------------------------------------------
     |  Getter Methods
     |----------------------------------------------------------------------------
     |
     | These methods were created to use on the app code, can be used by any User
     | Model Instance
     |
    */

    /**
     *  Get mutual friends with a user
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return array
     */
    public function getMutualFriends(Model $user, $perPage = 0)
    {
        return $this->getOrPaginate($this->getMutualFriendsQueryBuilder($user), $perPage);
    }

    /**
     *  Get mutual friends count
     */
    public function getMutualFriendsCount(Model $user)
    {
        return $this->getMutualFriendsQueryBuilder($user)->count();
    }

    /**
     *  Get friends off friends
     *
     * @return array
     */
    public function getFriendsOffFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getFriendsOffFriendsQueryBuilder(), $perPage);
    }

    /**
     *  Get a friendship between users
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return LengthAwarePaginator
     */
    public function getFriendship(Model $user, $perPage = 0)
    {
        return $this->findFriendship($user)->first();
    }

    /**
     *  Get all friends where status 'FRIEND'
     *
     * @param $status array|string
     * @param $senderStatus array|string
     * @param $recipientStatus array|string
     * @return array|null
     */
    public function getFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getFriendshipsQueryBuilder(null, null, [Status::FAVORITE, Status::FRIEND]), $perPage);
    }

    /**
     *  Get all friends however status
     *
     * @return array
     */
    public function getAllFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getFriendshipsQueryBuilder(), $perPage);
    }

    public function getFriendships($perPage = 0, $senderStatus = null, $recipientStatus = null, $status = [Status::FRIEND, Status::FAVORITE])
    {
        return $this->getOrPaginate($this->getFriendshipsQueryBuilder($senderStatus, $recipientStatus, $status), $perPage);
    }

    /**
     *  Get all pending requests
     *
     * @return array
     */
    public function getPendingRequests()
    {
        return $this->getOrPaginate($this->getFriendsWhereRecipientQueryBuilder($this, null, null, Status::PENDING));
    }

    /**U
     *  Get all pending responses
     *
     * @return array
     */
    public function getPendingResponses()
    {
        return $this->getOrPaginate($this->getFriendsWhereSenderQueryBuilder($this, null, null, Status::PENDING));
    }

    /**
     *  Get all blocked frieends
     *
     * @return array
     */
    public function getBlockedFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getWhereOtherStatusQueryBuilder(Status::BLOCKED), $perPage);
    }

    /**
     *  Get all blocker friends
     *
     * @return array
     */
    public function getBlockerFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getWhereThisStatusQueryBuilder(Status::BLOCKED), $perPage);
    }

    /**
     *  Get all favorite friends
     *
     * @return array
     */
    public function getFavoriteFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getWhereOtherStatusQueryBuilder(Status::FAVORITE), $perPage);
    }

    /**
     *  Get all friends who favorited you
     *
     * @return array
     */
    public function getFavoriterFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getWhereThisStatusQueryBuilder(Status::FAVORITE), $perPage);
    }

    /**
     * Return all common friends
     * @return array
     */
    public function getUnfavoriteFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getWhereThisStatusQueryBuilder(Status::FRIEND), $perPage);
    }

    /**
     *  Return general status between friends
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return int
     */
    public function getGeneralStatus(Model $user): int
    {
        if (!$friendship = $this->findFriendship($user)->first()) {
            return Status::STRANGER;
        }

        ['sender_status' => $sender,
            'recipient_status' => $recipient] = $friendship;

        if ($recipient == Status::FRIEND && $sender == Status::FRIEND) return Status::FRIEND;
        elseif ($recipient == Status::PENDING || $sender == Status::PENDING) return Status::PENDING;
        elseif ($recipient == Status::FAVORITE && $sender == Status::FAVORITE) return Status::FAVORITE;
        elseif ($recipient == Status::FRIEND && $sender == Status::FAVORITE ||
            $recipient == Status::FAVORITE && $sender == Status::FRIEND
        ) return Status::FRIEND;
        elseif ($recipient == Status::BLOCKED || $sender == Status::BLOCKED) return Status::BLOCKED;

        return Status::STRANGER;

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

    /*
    |----------------------------------------------------------------------------
    |  Checker Methods
    |----------------------------------------------------------------------------
    |
    | These methods were created to use on the app code, can be used by any User
    | Model Instance
    |
    */

    /**
     *  Check if user is friend of another user
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function isFriendOf(Model $user): bool
    {
        $status = $this->getGeneralStatus($user);
        return $status == Status::FRIEND || $status == Status::FAVORITE;
    }

    /**
     *  Check if there is a friendship between users
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function hasFriendshipWith(Model $user): bool
    {
        return $this->findFriendship($user)->exists();
    }

    /**
     *  Check if user is blocked by another user
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function isBlockedBy(Model $user, $friendship = null): bool
    {
        if (is_null($friendship)) {
            $friendship = $this->findFriendship($user)->first();
        }
        $status = $friendship[$this->findPosition($user, $friendship) . '_status'];

        return $status == Status::BLOCKED;
    }

    /**
     *  Check if user has blocked another user
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function hasBlocked(Model $user, $friendship = null): bool
    {
        if (is_null($friendship)) {
            $friendship = $this->findFriendship($user)->first();
        }
        $status = $friendship[$this->findFriendPosition($user, $friendship) . '_status'];

        return $status == Status::BLOCKED;
    }

    /**
     * Check if user can be friend of another user
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return boolean
     */
    public function canBeFriend(Model $user): bool
    {
        if ($this->hasFriendshipWith($user)) {
            return false;
        }

        if ($this->hasBlocked($user)) {
            $this->unblockFriendship($user);

            return true;
        }

        if ($this->isBlockedBy($user)) {
            return false;
        }

        return true;
    }


    /*
     |----------------------------------------------------------------------------
     |  Private Methods
     |----------------------------------------------------------------------------
     |
     | These methods were created just for use inside the package, should not be used
     | on the app code.
     |
    */

    /**
     *  Search a friendship between users
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    private function findFriendship(Model $user): Builder
    {
        return Friendship::betweenModels($this, $user);
    }

    /**
     *  Find all friendships matching a given status
     *
     * @param $senderStatus null|string|array
     * @param $recipientStatus null|string|array
     * @param $status null|string|array
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function findFriendships($senderStatus = null, $recipientStatus = null, $status = null)
    {
        $query = Friendship::where(function ($query) {
            $query->where(function ($q) {
                $q->whereSender($this);
            })->orWhere(function ($q) {
                $q->whereRecipient($this);
            });
        });

        if (!is_null($senderStatus)) {
            if (is_array($senderStatus)) {
                $query->whereSenderStatusIn($senderStatus);
            } else {
                $query->whereSenderStatus($senderStatus);
            }
        }

        if (!is_null($recipientStatus)) {
            if (is_array($senderStatus)) {
                $query->whereRecipientStatusIn($senderStatus);
            } else {
                $query->whereRecipientStatus($senderStatus);
            }
        }

        if (!is_null($status)) {
            if (is_array($status)) {
                $query->whereStatusIn($status);
            } else {
                $query->whereStatus($status);
            }
        }

        return $query;
    }

    /**
     *  Check if user is recipient in friendship
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @param $friendship \BRKsDeadPool\Friendship\Models\Friendship|null
     * @return boolean
     */
    private function isRecipient($user, Model $friendship = null): bool
    {
        if (!is_null($friendship)) {
            return $friendship->recipient == $this->getKey();
        }

        return $this->findFriendship($user)
            ->whereRecipient($this)->exists();
    }

    /**
     *  Check if user is sender in friendship
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @param $friendship \BRKsDeadPool\Friendship\Models\Friendship|null
     * @return boolean
     */
    private function isSender($user, Model $friendship = null): bool
    {
        if (!is_null($friendship)) {
            return $friendship->sender == $this->getKey();
        }

        return $this->findFriendship($user)
            ->whereSender($this)->exists();
    }

    /**
     *  Return the position off a friend on the relationship
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @param $friendship Friendship|null
     * @return string
     */
    private function findPosition($user, Model $friendship = null): string
    {
        if ($this->isSender($user, $friendship)) {
            return 'sender';
        } else {
            return 'recipient';
        }
    }

    /**
     *  Return the position off a friend on the relationship
     *
     * @param $user \Illuminate\Database\Eloquent\Model
     * @param $friendship Friendship|null
     * @return string
     */
    private function findFriendPosition($user, Model $friendship = null): string
    {
        if ($this->findPosition($user, $friendship) == 'sender') {
            return 'recipient';
        } else {
            return 'sender';
        }
    }

    /**
     * Get the query builder of the 'friend' model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getFriendshipsQueryBuilder($senderStatus = null, $recipientStatus = null, $status = null)
    {
        $friendships = $this->findFriendships($senderStatus, $recipientStatus, $status)->get(['sender', 'recipient']);
        $recipients = $friendships->pluck('recipient')->all();
        $senders = $friendships->pluck('sender')->all();

        return $this->where('id', '!=', $this->getKey())->whereIn('id', array_merge($recipients, $senders));
    }

    /**
     *  Return Friendships by model sender
     */
    private function getFriendsWhereSenderQueryBuilder(Model $sender, $senderStatus = null, $recipientStatus = null, $status = null)
    {
        $friendships = $this->findFriendships($senderStatus, $recipientStatus, $status)->whereSender($sender)->get(['sender', 'recipient']);
        $recipients = $friendships->pluck('recipient')->all();
        $senders = $friendships->pluck('sender')->all();

        return $this->where('id', '!=', $this->getKey())->whereIn('id', array_merge($recipients, $senders));
    }

    /**
     *  Return Friendships by model sender
     */
    private function getFriendsWhereRecipientQueryBuilder(Model $recipient, $senderStatus = null, $recipientStatus = null, $status = null)
    {
        $friendships = $this->findFriendships($senderStatus, $recipientStatus, $status)->whereRecipient($recipient)->get(['sender', 'recipient']);
        $recipients = $friendships->pluck('recipient')->all();
        $senders = $friendships->pluck('sender')->all();

        return $this->where('id', '!=', $this->getKey())->whereIn('id', array_merge($recipients, $senders));
    }

    /**
     *  Get all friends with a given status
     */
    private function getWhereOtherStatusQueryBuilder($status)
    {
        return $this->findFriendships(null, null, null)
            ->where(function ($query) use ($status) {
                return $query->whereSender($this)
                    ->whereRecipientStatus($status);
            })
            ->orWhere(function ($query) use ($status) {
                return $query->whereRecipient($this)
                    ->whereSenderStatus($status);
            });
    }

    /**
     *  Get all friends were this has a given status
     */
    private function getWhereThisStatusQueryBuilder($status)
    {
        return $this->findFriendships(null, null, null)
            ->where(function ($query) use ($status) {
                return $query->whereSender($this)
                    ->whereSenderStatus($status);
            })
            ->orWhere(function ($query) use ($status) {
                return $query->whereRecipient($this)
                    ->whereRecipientStatus($status);
            });
    }

    /**
     *  Return friends off friends query builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getFriendsOffFriendsQueryBuilder()
    {
        $friendships = $this->findFriendships([Status::FRIEND, Status::FAVORITE])->get(['sender', 'recipient']);
        $recipients = $friendships->pluck('recipient')->all();
        $senders = $friendships->pluck('sender')->all();
        $friendIds = array_unique(array_merge($recipients, $senders));
        $fofs = Friendship::whereStatusIn([Status::FRIEND, Status::FAVORITE])
            ->where(function ($query) use ($friendIds) {
                $query->where(function ($q) use ($friendIds) {
                    $q->whereIn('sender', $friendIds);
                })->orWhere(function ($q) use ($friendIds) {
                    $q->whereIn('recipient', $friendIds);
                });
            })->get(['sender', 'recipient']);


        $fofIds = array_unique(
            $fofs->map(function ($item) {
                return [$item->sender, $item->recipient];
            })->flatten()->all()
        );

        return $this->whereIn('id', $fofIds)->whereNotIn('id', $friendIds);
    }

    /**
     *  Get mutual friends with user
     *
     * @param $other \Illuminate\Database\Eloquent\Model
     */
    private function getMutualFriendsQueryBuilder(Model $other)
    {
        $user1['friendships'] = $this->findFriendships([Status::FRIEND, Status::FAVORITE])->get(['sender', 'recipient']);
        $user1['recipients'] = $user1['friendships']->pluck('recipient')->all();
        $user1['senders'] = $user1['friendships']->pluck('sender')->all();

        $user2['friendships'] = $other->findFriendships([Status::FRIEND, Status::FAVORITE])->get(['sender', 'recipient']);
        $user2['recipients'] = $user2['friendships']->pluck('recipient')->all();
        $user2['senders'] = $user2['friendships']->pluck('sender')->all();

        $mutualFriendships = array_unique(
            array_intersect(
                array_merge($user1['recipients'], $user1['senders']),
                array_merge($user2['recipients'], $user2['senders'])
            )
        );
        return $this->whereNotIn('id', [$this->getKey(), $other->getKey()])->whereIn('id', $mutualFriendships);
    }

    /**
     *  Get or paginate the data
     *
     * @param $builder \Illuminate\Database\Eloquent\Builder
     * @param $perPage int
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|array
     */
    private function getOrPaginate($builder, $perPage = 0)
    {
        if ($perPage == 0) {
            return $builder->get();
        }

        return $builder->paginate($perPage);
    }
}

// TODO: Return friends in all get methods instead of the friendship