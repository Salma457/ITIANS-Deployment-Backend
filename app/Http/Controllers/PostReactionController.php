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
    $reactions = PostReaction::with(['user', 'itianProfile', 'employerProfile'])
        ->where('post_id', $postId)
        ->get()
        ->groupBy('reaction_type')
        ->map(function ($reactionGroup) {
            return $reactionGroup->map(function ($reaction) {
                $user = $reaction->user;
                $name = $user->name;
                $avatar = null;

                if ($reaction->itianProfile) {
                    $name = $reaction->itianProfile->first_name . ' ' . $reaction->itianProfile->last_name;
                    $avatar = $reaction->itianProfile->profile_picture_url;
                } elseif ($reaction->employerProfile) {
                    $name = $reaction->employerProfile->company_name;
                    $avatar = $reaction->employerProfile->company_logo_url;
                }

                return [
                    'id' => $user->id,
                    'name' => $name,
                    'avatar' => $avatar,
                ];
            });
        });

    return response()->json($reactions);
}

}
