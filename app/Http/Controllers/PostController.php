<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ItianProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    // ✅ عرض كل البوستات
    public function index()
    {
        $posts = Post::with('itian')->latest()->get();
        return response()->json($posts);
    }
public function myPosts()
{
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $itianProfile = $user->itianProfile;

    if (!$itianProfile) {
        return response()->json(['message' => 'User has no ITI profile.'], 403);
    }

    $posts = Post::with('itian')
        ->where('itian_id', $itianProfile->itian_profile_id)
        ->latest()
        ->get();

    return response()->json($posts);
}


    // ✅ إنشاء بوست جديد
    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    $user = Auth::user();
    $itianProfile = $user->itianProfile;

    if (!$itianProfile) {
        return response()->json(['error' => 'User has no ITI profile.'], 403);
    }

    $post = Post::create([
        'itian_id' => $itianProfile->itian_profile_id,
        'title' => $request->title,
        'content' => $request->content,
    ]);

    // تحميل بيانات البروفايل المرتبطة
    $post->load('itian');

    return response()->json([
        'message' => 'Post created successfully',
        'data' => [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'itian_profile' => $post->itian, // هنا كل بيانات البروفايل
            'image' => $request->image,
    'tags' => $request->tags,
    'visibility' => $request->visibility ?? 'public',
        ]
    ], 201);
}


    // ✅ عرض بوست معين
    public function show($id)
    {
        $post = Post::with('itian')->findOrFail($id);
        return response()->json($post);
    }

    // ✅ تحديث بوست
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();

        // السماح بالتحديث فقط لصاحب البروفايل
        if ($post->itian->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $post->update($request->only('title', 'content'));

        return response()->json($post);
    }

    // ✅ حذف بوست
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();

        if ($post->itian->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
