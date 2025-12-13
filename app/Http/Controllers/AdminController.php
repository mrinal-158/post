<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use App\Notifications\JustNotify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function admin(Request $request){
        $admin = Auth::user();
        $admin->notify(new JustNotify('Welcome to the Admin Dashboard.'));
        return response()->json([
            'message' => 'Welcome to the Admin Dashboard',
        ]);
    }

    public function accessToAdmin(Request $request, $userID){
        $admin = Auth::user();
        $user = User::where('id', $userID)->first();
        if(!$user){
            $admin->notify(new JustNotify('User not found.'));
            return response()->json([
                'message' => 'User not found',
            ]);
        }
        $user->role = 'admin';
        $user->save();    
        $admin->notify(new JustNotify('User promoted to admin successfully.'));
        return response()->json([
            'message' => 'Access granted to admin: ' . $admin->name,
        ]);
    }

    public function deleteUser(Request $request, $userID){
        $admin = Auth::user();
        $user = User::where('id', $userID)->first();

        if(!$user){
            $admin->notify(new JustNotify('User not found.'));
            return response()->json([
                'message' => 'User not found',
            ]);
        }
        $user->delete();    
        $admin->notify(new JustNotify('User deleted successfully.'));
        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function deletePost(Request $request, $postID){
        $admin = Auth::user();
        $post = Post::where('id', $postID)->first();
        if(!$post){
            $admin->notify(new JustNotify('Post not found.'));
            return response()->json([
                'message' => 'Post not found',
            ]);
        }
        $post->delete();    
        $admin->notify(new JustNotify('Post deleted successfully.'));
        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }

    public function deleteComment(Request $request, $commentID){
        $admin = Auth::user();
        $comment = Comment::where('id', $commentID)->first();
        if(!$comment){
            $admin->notify(new JustNotify('Comment not found.'));
            return response()->json([
                'message' => 'Comment not found',
            ]);
        }
        $comment->delete();    
        $admin->notify(new JustNotify('Comment deleted successfully.'));
        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    public function deleteReply(Request $request, $replyID){
        $admin = Auth::user();
        $reply = Reply::where('id', $replyID)->first();
        if(!$reply){
            $admin->notify(new JustNotify('Reply not found.'));
            return response()->json([
                'message' => 'Reply not found',
            ]);
        }
        $reply->delete();    
        $admin->notify(new JustNotify('Reply deleted successfully.'));
        return response()->json([
            'message' => 'Reply deleted successfully',
        ]);
    }
}
