<?php

namespace BRKsDeadPool\Friendship\Traits;

use BRKsDeadPool\Friendship\Codes\Status;
use BRKsDeadPool\Friendship\Events\FriendshipAccepted;
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
     * @return integer
     */
    public function getFriendsCount()
    {
        return $this->findFriendships(Status::FRIEND)->count();
    }

    /**
     * @param $recipient \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public function canBeFriend(Model $recipient)
    {
        return true;
    }

    /**
     * @param $status integer
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getAllFriendships($status = null)
    {
        return $this->findFriendships($status)->get();
    }

    /**
     * @param $other \Illuminate\Database\Eloquent\Model
     * @param $perPage integer
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getMutualFriends(Model $other, $perPage = 0)
    {
        return $this->getOrPaginate($this->getMutualFriendsQueryBuilder($other), $perPage);
    }

    /**
     * @param $recipient \Illuminate\Database\Eloquent\Model
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    public function getFriendship(Model $recipient)
    {
        return $this->findFriendship($recipient)->first();
    }

    /**
     * @param $perPage integer
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getFriendsQueryBuilder(), $perPage);
    }

    /**
     * @param $other \Illuminate\Database\Eloquent\Model
     * @return integer
     */
    public function getMutualFriendsCount($other)
    {
        return $this->getMutualFriendsQueryBuilder($other)->count();
    }

    /**
     * @param $perPage integer
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getFriendsOffFriends($perPage = 0)
    {
        return $this->getOrPaginate($this->getFriendsOffFriendsQueryBuilder(), $perPage);
    }

    /**
     * @param $recipient \Illuminate\Database\Eloquent\Model
     *
     * @return boolean
     */
    public function isSender(Model $recipient)
    {
        if ($friendship = $this->getFriendship($recipient)) {
            $sender = $friendship->sender;

            return $sender->getKey() == $this->getKey() &&
                $sender->getMorphClass() == $this->getMorphClass();
        }

        return false;
    }

    /**
     * @param $recipient \Illuminate\Database\Eloquent\Model
     *
     * @return boolean
     */
    public function isRecipient(Model $recipient)
    {
        if ($friendship = $this->getFriendship($recipient)) {
            $recipient = $friendship->recipient;

            return $recipient->getKey() == $this->getKey() &&
                $recipient->getMorphClass() == $this->getMorphClass();
        }

        return false;
    }

    public function acceptFriendship(Model $recipient) {
        event(new FriendshipAccepted($this, $recipient));

        return $this->findFriendship($recipient)
            ->whereRecipient($this)
            ->update((new Friendship)->fillStatus(Status::FRIEND)->toArray());
    }



    /**
     *  Get or paginate the data
     */
    private function getOrPaginate($builder, $perPage = 0)
    {
        if ($perPage = 0) {
            return $builder->get();
        }

        return $builder->paginate($perPage);
    }

    /**
     * @param $recipient \Illuminate\Database\Eloquent\Model
     * @return \BRKsDeadPool\Friendship\Models\Friendship
     */
    private function findFriendship(Model $recipient)
    {
        return Friendship::betweenModels($this, $recipient);
    }

    /**
     * @param $status int;
     * @return Friendship;
     */
    private function findFriendships($status = null)
    {
        $query = Friendship::where(function ($query) {
            $query->where(function ($q) {
                $q->whereSender($this);
            })->orWhere(function ($q) {
                $q->whereRecipient($this);
            });
        });

        if (!is_null($status)) $query->where('sender_status', $status)
            ->where('recipient_status', $status);

        return $query;
    }

    /**
     * Get the query builder of the 'friend' model
     *
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getFriendsQueryBuilder()
    {
        $friendships = $this->findFriendships(Status::FRIEND)->get(['sender_id', 'recipient_id']);
        $recipients = $friendships->pluck('recipient_id')->all();
        $senders = $friendships->pluck('sender_id')->all();

        return $this->where('id', '!=', $this->getKey())->whereIn('id', array_merge($recipients, $senders));
    }

    /**
     * Get the query builder of the 'friend' model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getMutualFriendsQueryBuilder(Model $other)
    {
        $user1['friendships'] = $this->findFriendships(Status::FRIEND)->get(['sender_id', 'recipient_id']);
        $user1['recipients'] = $user1['friendships']->pluck('recipient_id')->all();
        $user1['senders'] = $user1['friendships']->pluck('sender_id')->all();

        $user2['friendships'] = $other->findFriendships(Status::FRIEND)->get(['sender_id', 'recipient_id']);
        $user2['recipients'] = $user2['friendships']->pluck('recipient_id')->all();
        $user2['senders'] = $user2['friendships']->pluck('sender_id')->all();

        $mutualFriendships = array_unique(
            array_intersect(
                array_merge($user1['recipients'], $user1['senders']),
                array_merge($user2['recipients'], $user2['senders'])
            )
        );
        return $this->whereNotIn('id', [$this->getKey(), $other->getKey()])->whereIn('id', $mutualFriendships);
    }

    private function getFriendsOffFriendsQueryBuilder()
    {
        $friendships = $this->findFriendships(Status::FRIEND)->get(['sender_id', 'recipient_id']);
        $recipients = $friendships->pluck('recipient_id')->all();
        $senders = $friendships->pluck('sender_id')->all();
        $friendIds = array_unique(array_merge($recipients, $senders));
        $fofs = Friendship::where('status', Status::FRIEND)
            ->where(function ($query) use ($friendIds) {
                $query->where(function ($q) use ($friendIds) {
                    $q->whereIn('sender_id', $friendIds);
                })->orWhere(function ($q) use ($friendIds) {
                    $q->whereIn('recipient_id', $friendIds);
                });
            })->get(['sender_id', 'recipient_id']);


        $fofIds = array_unique(
            $fofs->map(function ($item) {
                return [$item->sender_id, $item->recipient_id];
            })->flatten()->all()
        );

        return $this->whereIn('id', $fofIds)->whereNotIn('id', $friendIds);
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