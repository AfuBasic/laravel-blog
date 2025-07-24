<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(4);

        return response()->json(['data' => $posts, 'status' => true]);
    }

    public function getPostBySlug($slug)
    {
        $post_category = PostCategory::where('slug', $slug)->first();
        if (!$post_category) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

        $posts = $post_category->posts()
            ->where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['data' => $posts, 'status' => true]);
    }

    public function getPost($slug)
    {
        $post = Post::where('slug', $slug)->first();
        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

        $post->related_posts = $post->getRelatedPosts();

        return response()->json([
            'data' => $post,
            'status' => true
        ]);
    }
}
