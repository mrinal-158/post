<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Notifications\JustNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Request $request, $postID){
        $user = Auth::user();
        
        $post = Post::findOrFail($postID);

        if($post->isLikedBy($user->id)){
            $post->likes()->where('user_id', $user->id)->delete();

            $user->notify(new JustNotify('You have unliked a post.'));
            return response()->json([
                'message' => 'Unliked',
            ]);
        }
        else{
            $post->likes()->create([
                'user_id' => $user->id,
            ]);

            $user->notify(new JustNotify('You have liked a post.'));
            return response()->json([
                'message' => 'Liked',
            ]);
        }
    }

    public function total(Request $request, $postID){
        $post = Post::findOrFail($postID);
        $author = User::where('id', $post->user_id)->first();
        $totalLikes = $post->likes()->count();

        return response()->json([
            'author' => $author->name,
            'post_id' => $postID,
            'total_likes' => $totalLikes,
        ]);
    }
}
