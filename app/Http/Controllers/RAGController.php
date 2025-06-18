<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenAIService;

class RAGController extends Controller
{
    public function askRAG(Request $request, OpenAIService $openAI)
    {
        $query = $request->input('question');
        $answer = $openAI->generateAnswer($query);

        return response()->json([
            'question' => $query,
            'answer' => $answer,
        ]);
    }
}
