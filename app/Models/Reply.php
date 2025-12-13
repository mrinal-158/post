<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{   
    protected $table = 'replys';
    // protected $fillable = ['comments_id', 'user_id', 'body'];
    protected $guarded = [];

    public function comment() {
        return $this->belongsTo(Comment::class, 'comments_id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function isLikedBy($userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function likesCount()
    {
        return $this->likes()->count();
    }

    
}