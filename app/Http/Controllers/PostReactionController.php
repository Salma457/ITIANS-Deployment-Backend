<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostReactionController extends Controller
{
    public function react(Request $request, $postId)
    {
        $request->validate([
            'reaction_type' => 'required|in:like,love,haha,sad,angry,support,wow'
        ]);

        $user = Auth::user();

        // ✅ تأكد إن البوست موجود
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $reaction = PostReaction::updateOrCreate(
            ['post_id' => $postId, 'user_id' => $user->id],
            ['reaction_type' => $request->reaction_type]
        );

        return response()->json(['message' => 'Reaction saved', 'data' => $reaction]);
    }

    public function removeReaction($postId)
    {
        $user = Auth::user();

        // ✅ تأكد إن البوست موجود
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $deleted = PostReaction::where('post_id', $postId)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Reaction removed']);
    }

    public function getReactions($postId)
    {
        $post = Post::with('reactions')->find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $grouped = $post->reactions->groupBy('reaction_type')->map->count();

        return response()->json([
            'reactions' => $grouped,
            'user_reaction' => $post->reactions->firstWhere('user_id', Auth::id())?->reaction_type
        ]);
    }
    // PostReactionController.php
public function getReactionDetails($postId)
{
    $reactions = PostReaction::with('user')
        ->where('post_id', $postId)
        ->get()
        ->groupBy('reaction_type')
        ->map(function ($reactions) {
            return $reactions->map(function ($reaction) {
                return [
                    'id' => $reaction->user->id,
                    'name' => $reaction->user->name,
                    'avatar' => $reaction->user->profile_picture_url
                ];
            });
        });

    return response()->json($reactions);
}
}
