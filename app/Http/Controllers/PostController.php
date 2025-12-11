<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request){
        $posts = Post::where('user_id', '!=', auth()->id())
                ->orderByDesc('created_at')
                ->paginate(10);

        return response()->json([
            'posts' => $posts,
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
            'image'       => $post->image,
            'total_likes' => $post->likes_count,
            'comments'    => $post->comments->pluck('body'),
        ]);
    }

    public function create(Request $request){
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'nullable|image',
        ]);

        $user = auth()->user();
        
        $data = $request->only(['title', 'body']);
        $data['user_id'] = $user->id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'_'.$image->getClientOriginalName();
            $image->move(public_path('posts'), $name);
            $data['image'] = 'posts/'.$name;
        }

        $post = Post::create($data);

        return response()->json([
            'message' => 'Post created success!',
            'post' => $post,
        ]);
    }

    public function update(Request $request, $post_id){
        $user = auth()->user();
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

        return response()->json([
            'message' => 'Post updated success!',
            'post' => $post,
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
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

        return response()->json(['message' => 'Post deleted']);
    }

    public function myPosts()
    {
        $posts = auth()->user()->posts()->withCount('likes')->orderByDesc('created_at')->get();
        return response()->json($posts);
    }
}
