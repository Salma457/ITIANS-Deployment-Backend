<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ItianProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
  
public function index(Request $request)
{
    $userId = auth()->id();
    $filterUserId = $request->query('user_id'); // جلب user_id من الكويري سترينج

    $query = Post::with(['itian', 'reactions'])->latest();

    if ($filterUserId) {
        // فلترة البوستات حسب user_id للـ itian
        $query->whereHas('itian', function ($q) use ($filterUserId) {
            $q->where('user_id', $filterUserId);
        });
    }

    $paginated = $query->paginate(10);

    $posts = $paginated->getCollection()->map(function ($post) use ($userId) {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'image' => $post->image,
            'created_at' => $post->created_at,
            'itian' => [
                'id' => $post->itian->id ?? null,
                'first_name' => $post->itian->first_name ?? '',
                'last_name' => $post->itian->last_name ?? '',
                'profile_picture' => $post->itian->profile_picture ?? null,
                'user_id' => $post->itian->user_id ?? null,
            ],
            'user_reaction' => $post->reactions->firstWhere('user_id', $userId)?->reaction_type,
            'reactions' => $post->reactions
                ->groupBy('reaction_type')
                ->map(fn($group) => $group->count()),
        ];
    });

    return response()->json([
        'data' => $posts,
        'current_page' => $paginated->currentPage(),
        'last_page' => $paginated->lastPage(),
        'total' => $paginated->total(),
    ]);
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

    $paginated = Post::with('itian')
    ->where('itian_id', $itianProfile->itian_profile_id)
    ->latest()
    ->paginate(10);

return response()->json([
    'data' => $paginated->items(),
    'current_page' => $paginated->currentPage(),
    'last_page' => $paginated->lastPage(),
    'total' => $paginated->total(),
]);

}


   public function store(Request $request)
{
    $data = $request->validate([
        'title' => 'required|string',
        'content' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

    ]);

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('posts', 'public');
        $data['image'] = $imagePath;
    }

    $user = Auth::user();
    $itianProfile = $user->itianProfile;

    if (!$itianProfile) {
        return response()->json(['error' => 'User has no ITI profile.'], 403);
    }

    $post = Post::create([
    'itian_id' => $itianProfile->itian_profile_id,
    'title' => $data['title'],
    'content' => $data['content'],
    'image' => $data['image'] ?? null,

]);


    $post->load('itian');

    return response()->json([
        'message' => 'Post created successfully',
        'data' => $post
    ], 201);
}



    public function show($id)
    {
        $post = Post::with('itian')->findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, Post $post)
{
    $data = $request->validate([
        'title' => 'sometimes|required|string',
        'content' => 'sometimes|required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('posts', 'public');
        $data['image'] = $imagePath;
    }

    $user = Auth::user();

        if ($post->itian->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $post->update($data);

        return response()->json($post);
    }

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
