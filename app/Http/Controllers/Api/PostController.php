<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // List all posts
    public function index()
    {
        $posts = Post::orderByDesc('created_at')->get();
        return response()->json(['data' => $posts]);
    }

    // Show single post
    public function show($id)
    {
        $post = Post::findOrFail($id);
        return response()->json(['data' => $post]);
    }

    // Create new post
    public function store(Request $request)
    {
        if (! $request->user() || ! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'body'  => 'required|string',
        ]);
        $post = Post::create($data);
        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        if (! $request->user() || ! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $post = Post::findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'body'  => 'sometimes|required|string',
        ]);
        $post->update($data);
        return response()->json($post);
    }

    // Delete post
    public function destroy(Request $request, $id)
    {
        if (! $request->user() || ! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}