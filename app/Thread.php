<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Notifications\ThreadWasUpdated;
use App\Events\ThreadHasNewReply;

class Thread extends Model
{

    use RecordsActivity;

    protected $fillable = ['user_id', 'channel_id', 'title', 'body'];

    protected $with = ['creator'];

    protected $append = ['isSubscribedTo'];

    protected static function boot()
    {
        parent::boot();

        // Global query scope
        //For all thread queries, include the reply count
        /*static::addGlobalScope('replyCount', function($builder){
            $builder->withCount('replies');
        });*/

        static::deleting(function($thread) {
            $thread->replies->each->delete();
        });
    }



    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->id}";
    }

    public function replies()
    {
        return $this->hasMany(Reply::class)
                    ->with('owner');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function addReply($reply)
    {
        $reply = $this->replies()->create($reply);

        event(new ThreadHasNewReply($this, $reply));

        return $reply;
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    /*public function getReplyCountAttribute()
    {
        return $this->replies()->count();
    }*/

    public function subscribe($user_id = null)
    {
        $this->subscriptions()->create([
            'user_id' =>  $user_id ?: auth()->id()
        ]);

        return $this;
    }

    public function unsubscribe($user_id = null)
    {
        $this->subscriptions()
             ->where('user_id', $user_id ?: auth()->id())
             ->delete();
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()
                    ->where('user_id', auth()->id())
                    ->exists();
    }

    public function hasUpdatesFor($user)
    {
        $key = $user->visitedThreadCacheKey($this);
        return $this->updated_at > cache($key);
    }
}
