<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    public function subject()
    {
        return $this->morphTo();
    }

    public static function feed(User $user, $item_limit = 50)
    {
        return $user->activity()
                    ->latest()
                    ->with('subject')
                    ->take($item_limit)
                    ->get()
                    ->groupBy(function($activity){
                        return $activity->created_at->format('Y-m-d');
                    });
    }
}
