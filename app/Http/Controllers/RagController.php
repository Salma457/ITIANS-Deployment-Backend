<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RagEmbedderService;

class RagController extends Controller
{
    public function embedPosts()
    {
        $service = new RagEmbedderService();
        $service->processAllPosts();
        return response()->json(['message' => 'تم توليد Embeddings للبوستات']);
    }

    public function embedJobs()
    {
        $service = new RagEmbedderService();
        $service->processAllJobs();
        return response()->json(['message' => 'تم توليد Embeddings للوظائف']);
    }

    public function search(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json(['error' => 'يرجى إرسال نص البحث في parameter اسمه q'], 400);
        }

        $service = new RagEmbedderService();

        try {
            $results = $service->searchInSupabase($query);
            return response()->json($results);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function ask(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json(['error' => 'يرجى إرسال نص السؤال في parameter اسمه q'], 400);
        }

        $service = new RagEmbedderService();
        $answer = $service->askWithContext($query);

        return response()->json(['answer' => $answer]);
    }
}
