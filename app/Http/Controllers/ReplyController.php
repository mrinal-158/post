<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reply;
use App\Notifications\JustNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    public function create(Request $request, $commentID)
    {
        $user = Auth::user();

        $request->validate([
            'body' => 'required|string|max:255',
        ]);
        
        $data = $request->only(['body']);
        $data['user_id'] = $user->id;
        $data['comments_id'] = $commentID;
        
        $reply = Reply::create($data);

        $user->notify(new JustNotify('Your reply has been added successfully!'));
        return response()->json([
            'comments_id' => $commentID,
            'reply' => $request->body,
        ]);
    }

    public function show($commentID)
    {
        $comment = Comment::with(['user', 'replies.user'])
                ->withCount('likes')
                ->findOrFail($commentID);
        return response()->json([
            'comment_id' => $comment->id,
            'replies' => $comment->replies->pluck('body'),
        ]);
    }

    public function update(Request $request, $replyID){
        $user = Auth::user();

        $reply = Reply::where('id', $replyID)->first();
        if(!$reply){
            $user->notify(new JustNotify('Attempted to update a non-existent reply!'));
            return response()->json([
                'message' => 'Reply already deleted!',
            ]);
        }
        if($reply->user_id != $user->id){
            $user->notify(new JustNotify('Unauthorized attempt to update reply!'));
            return response()->json([
                'message' => 'Unauthorized reply to update!',
            ]);
        }

        $request->validate([
            'body' => 'required|string|max:255',
        ]);

        $reply->body = $request->body;
        $reply->save();

        $user->notify(new JustNotify('Your reply has been updated successfully!'));
        return response()->json([
            'message' => 'Reply update success!',
            'body' => $request->body,
        ]);
    }

    public function destroy($replyID)
    {
        $user = Auth::user();
        
        $reply = Reply::where('id', $replyID)->first();
        if($reply->user_id != $user->id){
            $user->notify(new JustNotify('Unauthorized attempt to delete reply!'));
            return response()->json([
                'message' => 'Unauthorized reply to delete!',
            ]);
        }

        $reply->delete();

        $user->notify(new JustNotify('Your reply has been deleted successfully!'));
        return response()->json([
            'message' => 'Reply delete success!',
        ]);
    }
}
