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
        return response()->json(['message' => 'Embeddings for posts have been generated successfully.']);
    }

    public function embedJobs()
    {
        $service = new RagEmbedderService();
        $service->processAllJobs();
        return response()->json(['message' => 'Embeddings for jobs have been generated successfully.']);
    }

    public function search(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json(['error' => 'Please provide a search query using the "q" parameter.'], 400);
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
            return response()->json(['error' => 'Please provide a question using the "q" parameter.'], 400);
        }

        $service = new RagEmbedderService();
        $answer = $service->askWithContext($query, 'gemini');

        return response()->json(['answer' => $answer]);
    }
}
