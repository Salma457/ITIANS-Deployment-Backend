<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // ✅ Get comments with replies + pagination
    public function index(Request $request, $postId)
{
    $perPage = $request->query('per_page', 5);

    $comments = Comment::where('post_id', $postId)
        ->whereNull('parent_comment_id')
        ->with(['replies.user.itianProfile', 'user.itianProfile'])
        ->latest()
        ->paginate($perPage);

    $data = $comments->getCollection()->map(function ($comment) {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at,
            'user' => [
                'id' => $comment->user->id,
                'name' => optional($comment->user->itianProfile)->first_name
                        ? optional($comment->user->itianProfile)->first_name . ' ' . optional($comment->user->itianProfile)->last_name
                        : $comment->user->name,
                'profile_picture' => optional($comment->user->itianProfile)->profile_picture,
                'iti_track' => optional($comment->user->itianProfile)->iti_track,
                'graduation_year' => optional($comment->user->itianProfile)->graduation_year,
                'linkedin' => optional($comment->user->itianProfile)->linkedin_profile_url,
                'github' => optional($comment->user->itianProfile)->github_profile_url,
            ],
            'replies' => $comment->replies->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'content' => $reply->content,
                    'created_at' => $reply->created_at,
                    'user' => [
                        'id' => $reply->user->id,
                        'name' => optional($reply->user->itianProfile)->first_name
                                ? optional($reply->user->itianProfile)->first_name . ' ' . optional($reply->user->itianProfile)->last_name
                                : $reply->user->name,
                        'profile_picture' => optional($reply->user->itianProfile)->profile_picture,
                        'iti_track' => optional($reply->user->itianProfile)->iti_track,
                        'graduation_year' => optional($reply->user->itianProfile)->graduation_year,
                        'linkedin' => optional($reply->user->itianProfile)->linkedin_profile_url,
                        'github' => optional($reply->user->itianProfile)->github_profile_url,
                    ],
                ];
            }),
        ];
    });

    return response()->json([
        'data' => $data,
        'current_page' => $comments->currentPage(),
        'last_page' => $comments->lastPage(),
        'per_page' => $comments->perPage(),
        'total' => $comments->total(),
    ]);
}


    // ✅ Create comment or reply
    public function store(Request $request, $postId)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'content' => 'required|string',
            'parent_comment_id' => 'nullable|exists:comments,id',
        ]);

        $post = Post::findOrFail($postId);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'parent_comment_id' => $request->input('parent_comment_id'),
        ]);

        $comment->load(['user.itianProfile']);

        return response()->json([
    'message' => 'Comment created successfully',
    'comment' => [
        'id' => $comment->id,
        'content' => $comment->content,
        'created_at' => $comment->created_at,
        'user' => [
            'id' => $comment->user->id,
            'name' => optional($comment->user->itianProfile)->first_name
                    ? optional($comment->user->itianProfile)->first_name . ' ' . optional($comment->user->itianProfile)->last_name
                    : $comment->user->name,
            'profile_picture' => optional($comment->user->itianProfile)->profile_picture,
            'iti_track' => optional($comment->user->itianProfile)->iti_track,
            'graduation_year' => optional($comment->user->itianProfile)->graduation_year,
            'linkedin' => optional($comment->user->itianProfile)->linkedin_profile_url,
            'github' => optional($comment->user->itianProfile)->github_profile_url,
        ],
        'replies' => [],
    ]
], 201);

    }

    // ✅ Update comment or reply
   public function update(Request $request, $id)
{
    $comment = Comment::findOrFail($id);

    if ($comment->user_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate([
        'content' => 'required|string',
    ]);

    $comment->update([
        'content' => $request->input('content'),
    ]);

    $comment->load('user.itianProfile');

    return response()->json([
        'message' => 'Comment updated successfully',
        'comment' => [
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at,
            'user' => [
                'id' => $comment->user->id,
                'name' => optional($comment->user->itianProfile)->first_name
                        ? optional($comment->user->itianProfile)->first_name . ' ' . optional($comment->user->itianProfile)->last_name
                        : $comment->user->name,
                'profile_picture' => optional($comment->user->itianProfile)->profile_picture,
                'iti_track' => optional($comment->user->itianProfile)->iti_track,
                'graduation_year' => optional($comment->user->itianProfile)->graduation_year,
                'linkedin' => optional($comment->user->itianProfile)->linkedin_profile_url,
                'github' => optional($comment->user->itianProfile)->github_profile_url,
            ],
        ]
    ]);
}


    // ✅ Delete comment or reply (with nested replies if it's a parent comment)
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $post = $comment->post;

        if ($comment->user_id !== Auth::id() && $post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (is_null($comment->parent_comment_id)) {
            $comment->replies()->delete();
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    // ✅ Update reply only
   public function updateReply(Request $request, $replyId)
{
    $reply = Comment::whereNotNull('parent_comment_id')->findOrFail($replyId);

    if ($reply->user_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate(['content' => 'required|string']);
    $reply->update(['content' => $request->input('content')]);
    $reply->load('user.itianProfile');

    return response()->json([
        'message' => 'Reply updated successfully',
        'reply' => [
            'id' => $reply->id,
            'content' => $reply->content,
            'created_at' => $reply->created_at,
            'user' => [
                'id' => $reply->user->id,
                'name' => optional($reply->user->itianProfile)->first_name
                        ? optional($reply->user->itianProfile)->first_name . ' ' . optional($reply->user->itianProfile)->last_name
                        : $reply->user->name,
                'profile_picture' => optional($reply->user->itianProfile)->profile_picture,
                'iti_track' => optional($reply->user->itianProfile)->iti_track,
                'graduation_year' => optional($reply->user->itianProfile)->graduation_year,
                'linkedin' => optional($reply->user->itianProfile)->linkedin_profile_url,
                'github' => optional($reply->user->itianProfile)->github_profile_url,
            ],
        ],
    ]);
}

    // ✅ Delete reply only
    public function destroyReply($replyId)
    {
        $reply = Comment::whereNotNull('parent_comment_id')->findOrFail($replyId);

        if ($reply->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reply->delete();

        return response()->json(['message' => 'Reply deleted successfully']);
    }
}
