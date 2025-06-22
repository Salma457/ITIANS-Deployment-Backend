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
            ->with(['replies.user', 'user'])
            ->latest()
            ->paginate($perPage);

        $data = $comments->getCollection()->map(function ($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'profile_picture' => $comment->user->profile_picture,
                ],
                'replies' => $comment->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'created_at' => $reply->created_at,
                        'user' => [
                            'id' => $reply->user->id,
                            'name' => $reply->user->name,
                            'profile_picture' => $reply->user->profile_picture,
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

        $comment->load(['user', 'replies.user']);

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'profile_picture' => $comment->user->profile_picture,
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

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment,
        ]);
    }

    // ✅ Delete comment or reply
   // ✅ Delete comment or reply + delete all its replies if it's a comment
public function destroy($id)
{
    $comment = Comment::findOrFail($id);
    $post = $comment->post;

    if ($comment->user_id !== Auth::id() && $post->user_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ✅ لو تعليق وليس رد ➜ امسح كل الردود المرتبطة بيه أولًا
    if (is_null($comment->parent_comment_id)) {
        $comment->replies()->delete();
    }

    $comment->delete();

    return response()->json(['message' => 'Comment deleted successfully']);
}


    // ✅ Optional: Separate route for updating a reply
   // ✅ updateReply - ترجع بيانات الرد كاملة مع بيانات المستخدم
public function updateReply(Request $request, $replyId)
{
    $reply = Comment::whereNotNull('parent_comment_id')->findOrFail($replyId);

    if ($reply->user_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate(['content' => 'required|string']);
    $reply->update(['content' => $request->input('content')]);
    $reply->load('user'); // علشان نضمن بيانات الـ user

    return response()->json([
        'message' => 'Reply updated successfully',
        'reply' => [
            'id' => $reply->id,
            'content' => $reply->content,
            'created_at' => $reply->created_at,
            'user' => [
                'id' => $reply->user->id,
                'name' => $reply->user->name,
                'profile_picture' => $reply->user->profile_picture,
            ],
        ]
    ]);
}


    // ✅ Optional: Separate route for deleting a reply
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
