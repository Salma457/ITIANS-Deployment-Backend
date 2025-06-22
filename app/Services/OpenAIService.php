<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;
    protected $supabaseUrl;
    protected $supabaseKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_API_KEY');
    }

   public function fetchDocumentsFromSupabase()
{
    $response = Http::withHeaders([
        'apikey' => $this->supabaseKey,
        'Authorization' => 'Bearer ' . $this->supabaseKey,
    ])->get("{$this->supabaseUrl}/rest/v1/" . config('services.supabase.table'), [
        'select' => '*',
    ]);

    \Log::info('Supabase Data:', $response->json());

    return $response->successful() ? $response->json() : [];
}

public function generateAnswer($question)
{
    $documents = $this->fetchDocumentsFromSupabase();

    $context = collect($documents)->pluck('description')->implode("\n");

    \Log::info('Context Sent to OpenAI', [$context]);

    $model = config('services.openai.model', 'o1-mini');

    $body = [
        'model' => $model,
        'messages' => [
            [
                'role' => 'user',
                'content' => "Use the following context to answer the question:\n\n$context\n\nQuestion: $question",
            ],
        ],
        // اختلاف حسب الموديل
        str_starts_with($model, 'o1-') ? 'max_completion_tokens' : 'max_tokens' => 300,
        'temperature' => 0.7,
    ];

    $response = Http::withToken($this->apiKey)
        ->post('https://api.openai.com/v1/chat/completions', $body);

    \Log::info('OpenAI API Response', [$response->json()]);

    $responseData = $response->json();

    return $responseData['choices'][0]['message']['content']
        ?? $responseData['error']['message']
        ?? 'No response from OpenAI.';
}

}
