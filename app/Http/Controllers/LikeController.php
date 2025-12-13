<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use App\Notifications\JustNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function postToggle(Request $request, $postID){
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

    public function postTotal(Request $request, $postID){
        $post = Post::findOrFail($postID);
        $author = User::where('id', $post->user_id)->first();
        $totalLikes = $post->likes()->count();

        return response()->json([
            'author' => $author->name,
            'post_id' => $postID,
            'total_likes' => $totalLikes,
        ]);
    }

    public function commentToggle(Request $request, $commentID){
        $user = Auth::user();
        
        $comment = Comment::findOrFail($commentID);
        if($comment->isLikedBy($user->id)){
            $comment->likes()->where('user_id', $user->id)->delete();

            $user->notify(new JustNotify('You have unliked a comment.'));
            return response()->json([
                'message' => 'Unliked',
            ]);
        }
        else{
            $comment->likes()->create([
                'user_id' => $user->id,
            ]);

            $user->notify(new JustNotify('You have liked a comment.'));
            return response()->json([
                'message' => 'Liked',
            ]);
        }
    }

    public function commentTotal(Request $request, $commentID){
        $user = Auth::user();
        $comment = Comment::where('id', $commentID)->first();
        if(!$comment){
            $user->notify(new JustNotify('Attempted to access likes for a non-existent comment!'));
            return response()->json([
                'message' => 'Comment not found!',
            ]);
        }

        $totalLikes = $comment->likes()->count();

        return response()->json([
            'comment_id' => $commentID,
            'total_likes' => $totalLikes,
        ]);
    }

    public function replyToggle(Request $request, $replyID){
        $user = Auth::user();
        
        $reply = Reply::findOrFail($replyID);

        if($reply->isLikedBy($user->id)){
            $reply->likes()->where('user_id', $user->id)->delete();

            $user->notify(new JustNotify('You have unliked a reply.'));
            return response()->json([
                'message' => 'Unliked',
            ]);
        }
        else{
            $reply->likes()->create([
                'user_id' => $user->id,
            ]);

            $user->notify(new JustNotify('You have liked a reply.'));
            return response()->json([
                'message' => 'Liked',
            ]);
        }
    }

    public function replyTotal(Request $request, $replyID){
        $reply = Reply::findOrFail($replyID);
        $author = User::where('id', $reply->user_id)->first();
        $totalLikes = $reply->likes()->count();

        return response()->json([
            'author' => $author->name,
            'reply_id' => $replyID,
            'total_likes' => $totalLikes,
        ]);     
    }
}
