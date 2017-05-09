<?php

namespace BRKsDeadPool\Friendship\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Friendship extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recipient_id',
        'sender_id',
        'recipient_type',
        'sender_type',
        'recipient_status',
        'sender_status',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function sender()
    {
        return $this->morphTo('sender');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function recipient()
    {
        return $this->morphTo('recipient');
    }

    /**
     *  Fill The recipient to create new friendship
     *
     * @param $recipient Model
     * @return Friendship
     */
    public function fillRecipient(Model $recipient)
    {
        return $this->fill([
            'recipient_id' => $recipient->getKey(),
            'recipient_type' => $recipient->getMorphClass()
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
     *  return all records where sender equals to some model
     *
     * @return \Illuminate\Database\Eloquent\Builder;
     */
    public function scopeWhereSender(Builder $query, Model $model)
    {
        return $query->where('sender_id', $model->getKey())->where('sender_type', $model->getMorphClass());
    }

    /**
     *  return all records where recipient equals to some model
     *
     * @return \Illuminate\Database\Eloquent\Builder;
     */
    public function scopeWhereRecipient(Builder $query, Model $model)
    {
        return $query->where('recipient_id', $model->getKey())->where('recipient_type', $model->getMorphClass());
    }

    public function scopeBetweenModels(Builder $query, $sender, $recipient)
    {
        $query->where(function ($queryIn) use ($sender, $recipient) {
            $queryIn->where(function ($q) use ($sender, $recipient) {
                $q->whereSender($sender)->whereRecipient($recipient);
            })->orWhere(function ($q) use ($sender, $recipient) {
                $q->whereSender($recipient)->whereRecipient($sender);
            });
        });
    }


}