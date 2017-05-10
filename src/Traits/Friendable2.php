<?php

namespace BRKsDeadPool\Friendship\Traits;

use BRKsDeadPool\Friendship\Codes\Status;
use BRKsDeadPool\Friendship\Events\FriendshipAccepted;
use BRKsDeadPool\Friendship\Events\FriendshipBlocked;
use BRKsDeadPool\Friendship\Events\FriendshipDenied;
use BRKsDeadPool\Friendship\Events\FriendshipFavorited;
use BRKsDeadPool\Friendship\Events\FriendshipRejected;
use BRKsDeadPool\Friendship\Events\FriendshipSent;
use BRKsDeadPool\Friendship\Events\FriendshipUnblocked;
use BRKsDeadPool\Friendship\Events\FriendshipUnfavorited;
use BRKsDeadPool\Friendship\Models\Friendship;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\AssignOp\Mod;

trait Friendable2
{
    /**
     *  Start a new Friendship with a Model
     *
     * @param $recipient \Illuminate\Database\Eloquent\Model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function beFriend(Model $recipient)
    {
        $friendship = (new Friendship)->fillRecipient($recipient);

        return $this->friendsFrom()->updateOrCreate($friendship->toArray(),
            $friendship->fillStatus(Status::PENDING)->toArray());
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
     * @param $status int|null|array;
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
     * Get the query builder of the 'friend' model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getFriendshipsQueryBuilder()
    {
        $friendships = $this->findFriendships([Status::FAVORITE, Status::FRIEND])->get(['sender_id', 'recipient_id']);
        $recipients = $friendships->pluck('recipient_id')->all();
        $senders = $friendships->pluck('sender_id')->all();

        return $this->where('id', '!=', $this->getKey())->whereIn('id', array_merge($recipients, $senders));
    }

    /**
     * Get the query builder of the 'friend' model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getMutualFriendshipsQueryBuilder(Model $other)
    {
        $user1['friendships'] = $this->findFriendships([Status::FRIEND, Status::FAVORITE])->get(['sender_id', 'recipient_id']);
        $user1['recipients'] = $user1['friendships']->pluck('recipient_id')->all();
        $user1['senders'] = $user1['friendships']->pluck('sender_id')->all();

        $user2['friendships'] = $other->findFriendships([Status::FRIEND, Status::FAVORITE])->get(['sender_id', 'recipient_id']);
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

    /**
     *  Return friends of friends query builder
     */
    private function getFriendsOffFriendsQueryBuilder()
    {
        $friendships = $this->findFriendships([Status::FRIEND, Status::FAVORITE])->get(['sender_id', 'recipient_id']);
        $recipients = $friendships->pluck('recipient_id')->all();
        $senders = $friendships->pluck('sender_id')->all();
        $friendIds = array_unique(array_merge($recipients, $senders));
        $fofs = Friendship::whereIn('status', [Status::FRIEND, Status::FAVORITE])
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