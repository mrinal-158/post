<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function create(Request $request, $postID)
    {
        $request->validate([
            'body' => 'required|string|max:255',
        ]);
        
        $data = $request->only(['body']);
        $data['user_id'] = Auth::user()->id;
        $data['post_id'] = $postID;
        
        $comment = Comment::create($data);

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
        $comment = Comment::where('id', $commentID)->first();
        $user = auth()->user();
        if($comment->user_id != $user->id){
            return response()->json([
                'message' => 'Unauthorized comment to update!',
            ]);
        }

        $request->validate([
            'body' => 'required|string|max:255',
        ]);

        $comment->body = $request->body;
        $comment->save();

        return response()->json([
            'message' => 'Comment update success!',
            'body' => $request->body,
        ]);
    }

    public function destroy($commentID)
    {
        $comment = Comment::where('id', $commentID)->first();
        $user = auth()->user();
        if($comment->user_id != $user->id){
            return response()->json([
                'message' => 'Unauthorized comment to delete!',
            ]);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment delete success!',
        ]);
    }
}
