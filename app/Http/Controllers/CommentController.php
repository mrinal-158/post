<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\JustNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function create(Request $request, $postID)
    {
        $user = Auth::user();

        $request->validate([
            'body' => 'required|string|max:255',
        ]);
        
        $data = $request->only(['body']);
        $data['user_id'] = $user->id;
        $data['post_id'] = $postID;
        
        $comment = Comment::create($data);

        $user->notify(new JustNotify('Your comment has been added successfully!'));
        return response()->json([
            'post_id' => $postID,
            'comment' => $request->body,
        ]);
    }

    public function show($postID)
    {
        $post = Post::with(['user', 'comments.user'])
                ->withCount('likes')
                ->findOrFail($postID);
        return response()->json([
            'post_id' => $post->id,
            'comments' => $post->comments->pluck('body'),
        ]);
    }

    public function update(Request $request, $commentID){
        $user = Auth::user();

        $comment = Comment::where('id', $commentID)->first();
        if(!$comment){
            $user->notify(new JustNotify('Attempted to update a non-existent comment!'));
            return response()->json([
                'message' => 'Comment already deleted!',
            ]);
        }
        if($comment->user_id != $user->id){
            $user->notify(new JustNotify('Unauthorized attempt to update a comment!'));
            return response()->json([
                'message' => 'Unauthorized comment to update!',
            ]);
        }

        $request->validate([
            'body' => 'required|string|max:255',
        ]);

        $comment->body = $request->body;
        $comment->save();

        $user->notify(new JustNotify('Your comment has been updated successfully!'));
        return response()->json([
            'message' => 'Comment update success!',
            'body' => $request->body,
        ]);
    }

    public function destroy($commentID)
    {
        $user = Auth::user();
        
        $comment = Comment::where('id', $commentID)->first();
        if(!$comment){
            $user->notify(new JustNotify('Attempted to delete a non-existent comment!'));
            return response()->json([
                'message' => 'Comment already deleted!',
            ]);
        }
        if($comment->user_id != $user->id){
            $user->notify(new JustNotify('Unauthorized attempt to delete a comment!'));
            return response()->json([
                'message' => 'Unauthorized comment to delete!',
            ]);
        }

        $comment->delete();

        $user->notify(new JustNotify('Your comment has been deleted successfully!'));
        return response()->json([
            'message' => 'Comment delete success!',
        ]);
    }
}
