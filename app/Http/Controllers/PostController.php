<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Notifications\JustNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(Request $request){
        $posts = Post::orderByDesc('created_at')
                ->withCount('likes')
                ->with(['user'])
                ->get();

        $result = [];

        foreach ($posts as $post) {
            $result[] = [
                'post_id'     => $post->id,
                'author_name' => $post->user->name,
                'title'       => $post->title,
                'body'        => $post->body,
                'image'       => $post->image,
                'total_likes' => $post->likes_count,
                'created_at'  => $post->created_at->toDateTimeString(),
            ];
        }

        return response()->json([
            'posts' => $result
        ]);
    }

    public function show($postID)
    {
        $post = Post::with(['user', 'comments.user'])
                ->withCount('likes')
                ->findOrFail($postID);

        return response()->json([
            'post_id'     => $post->id,
            'author_name' => $post->user->name,
            'title'       => $post->title,
            'body'        => $post->body,
            'total_likes' => $post->likes_count,
            'comments'    => $post->comments->pluck('body'),
        ]);
    }

    public function create(Request $request){
        $user = Auth::user();
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'nullable|image',
        ]);
        
        $data = $request->only(['title', 'body']);
        $data['user_id'] = $user->id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'_'.$image->getClientOriginalName();
            $image->move(public_path('posts'), $name);
            $data['image'] = 'posts/'.$name;
        }

        $post = Post::create($data);

        $user->notify(new JustNotify('Your post has been created successfully!'));
        return response()->json([
            'message' => 'Post created success!',
            'post' => $post,
        ]);
    }

    public function update(Request $request, $post_id){
        $user = Auth::user();

        $post = Post::findOrFail($post_id);

        if($post->user_id != $user->id){
            return response()->json([
                'message' => 'Forbidden post!',
            ]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'nullable|image',
        ]);

        $data['title'] = $request->title;
        $data['body'] = $request->body;
        
        if($request->hasFile('image')){
            // delete old image
            if ($post->image && file_exists(public_path($post->image))) {
                unlink(public_path($post->image));
            }
            $image = $request->file('image');
            $name = time().'_'.$image->getClientOriginalName();
            $image->move(public_path('posts'), $name);
            $data['image'] = 'posts/'.$name;
        }

        $post->update($data);

        $user->notify(new JustNotify('Your post has been updated successfully!'));
        return response()->json([
            'message' => 'Post updated success!',
            'post' => $post,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $post = Post::findOrFail($id);

        if ($post->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden',
            ]);
        }

        if ($post->image && file_exists(public_path($post->image))) {
            unlink(public_path($post->image));
        }
        $post->delete();

        $user->notify(new JustNotify('Your post has been deleted successfully!'));
        return response()->json(['message' => 'Post deleted']);
    }

    public function myPosts(Request $request)
    {
        $user = Auth::user();
        $posts = Post::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->withCount('likes')
                ->with(['user'])
                ->get();
        
        $result = [];
        foreach ($posts as $post) {
            $result[] = [
                'post_id'     => $post->id,
                'title'       => $post->title,
                'body'        => $post->body,
                'total_likes' => $post->likes_count,
                'created_at'  => $post->created_at->toDateTimeString(),
            ];
        }

        return response()->json([
            'posts' => $result,
        ]);
    }
}
