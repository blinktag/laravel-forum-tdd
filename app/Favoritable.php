<?php

namespace App;

trait Favoritable
{

    protected static function bootFavoritable()
    {
        static::deleting(function ($model){
            $model->favorites->each->delete();
        });
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorited');
    }

    public function favorite()
    {
        $attributes = [ 'user_id' => auth()->id() ];

        if ($this->favorites()->where($attributes)->exists()) {
            return false;
        }

        $this->favorites()->create($attributes);
    }

    public function unfavorite()
    {
        $attributes = [ 'user_id' => auth()->id() ];

        $this->favorites()->where($attributes)->get()->each->delete();
    }

    public function isFavorited()
    {
        return $this->favorites
                    ->where('user_id', auth()->id())
                    ->count() > 0;
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }

    public function getIsFavoritedAttribute()
    {
        return $this->isFavorited();
    }
}
