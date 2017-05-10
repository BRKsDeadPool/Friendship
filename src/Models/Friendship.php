<?php

namespace BRKsDeadPool\Friendship\Models;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Friendship extends Model
{
    protected $fillable = [
        'sender',
        'sender_status',
        'recipient',
        'recipient_status',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(\App\User::class, 'sender');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipient()
    {
        return $this->belongsTo(\App\User::class, 'recipient');
    }

    /**
     *  Fill The recipient to create new friendship
     *
     * @param $recipient Model
     * @return Friendship
     */
    public function fillRecipient(User $user)
    {
        return $this->fill([
            'recipient' => $user->getKey(),
        ]);
    }

    /**
     *  Fill the status
     *
     * @param $status string
     * @return Friendship
     */
    public function fillStatus($status)
    {
        return $this->fill([
            'sender_status' => $status,
            'recipient_status' => $status
        ]);
    }

    /**
     *  Fill the status
     *
     * @param $status string
     * @return Friendship
     */
    public function fillSenderStatus($status)
    {
        return $this->fill([
            'sender_status' => $status
        ]);
    }

    /**
     *  Fill the status
     *
     * @param $status string
     * @return Friendship
     */
    public function fillRecipientStatus($status)
    {
        return $this->fill([
            'recipient_status' => $status
        ]);
    }

    /**
     * @param $query \Illuminate\Database\Eloquent\Builder;
     * @param $user \App\User;
     * @return void;
     */
    public function scopeWhereSender(Builder $query, User $user)
    {
        $query->where('sender_id', $user->getKey());
    }

    /**
     * @param $query \Illuminate\Database\Eloquent\Builder;
     * @param $user \App\User;
     * @return void;
     */
    public function scopeWhereRecipient(Builder $query, User $user)
    {
        $query->where('recipient_id', $user->getKey());
    }

    /**
     * @param $query \Illuminate\Database\Eloquent\Builder;
     * @param $sender \App\User
     * @param $recipient \App\User
     * @return void
     */
    public function scopeBetweenModels(Builder $query, User $sender, User $recipient)
    {
        $query->where(function ($queryIn) use ($sender, $recipient) {
            $queryIn->where(function ($q) use ($sender, $recipient) {
                $q->whereSender($sender)->whereRecipient($recipient);
            })->orWhere(function ($q) use ($sender, $recipient) {
                $q->whereSender($recipient)->whereRecipient($sender);
            });
        });
    }

    /**
     * @param $query \Illuminate\Database\Eloquent\Builder;
     * @param $status string
     * @return void
     */
    public function scopeWhereStatus($query, $status)
    {
        $query->where('sender_status', $status)
            ->where('recipient_status', $status);
    }

    /**
     * @param $query \Illuminate\Database\Eloquent\Builder;
     * @param $status string
     * @return void
     */
    public function scopeWhereStatusIn($query, $status)
    {
        $query->whereIn('sender_status', $status)
            ->whereIn('recipient_status', $status);
    }

    /**
     * @param $query \Illuminate\Database\Eloquent\Builder;
     * @param $status string
     * @return void
     */
    public function scopeWhereSenderStatus($query, $status)
    {
        $query->where('sender_status', $status);
    }

    /**
     * @param $query \Illuminate\Database\Eloquent\Builder;
     * @param $status string
     * @return void
     */
    public function scopeWhereRecipientStatus($query, $status)
    {
        $query->where('recipient_status', $status);
    }
}