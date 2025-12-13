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

    public function likes() {
        return $this->hasMany(Like::class, 'post_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function replys()
    {
        return $this->hasMany(Reply::class, 'comments_id');
    }
}