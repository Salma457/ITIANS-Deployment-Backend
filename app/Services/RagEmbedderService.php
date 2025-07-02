<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Post;
use App\Models\Job;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RagEmbedderService
{
    public function cleanText(string $text): string
    {
        return trim(strip_tags($text));
    }

    public function storeInSupabase(string $sourceType, $sourceId, array $data)
    {
        $columns = [
            'source_type', 'source_id', 'title', 'content',
            // job columns
            'job_title', 'description', 'requirements', 'qualifications', 'job_location', 'job_type', 'salary_range_min', 'salary_range_max', 'currency', 'posted_date', 'application_deadline',
            // post columns
            'post_title', 'post_content', 'views_count', 'likes_count', 'image', 'is_published', 'post_created_at', 'post_updated_at'
        ];
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $updateSet = implode(', ', array_map(fn($col) => "$col = EXCLUDED.$col", $columns));
        $sql = "
            INSERT INTO itian_rag_knowledge (" . implode(', ', $columns) . ")
            VALUES ($placeholders)
            ON CONFLICT (source_type, source_id)
            DO UPDATE SET $updateSet
        ";
        $values = [
            $sourceType,
            $sourceId,
            $data['title'] ?? null,
            $data['content'] ?? null,
            // job columns
            $data['job_title'] ?? null,
            $data['description'] ?? null,
            $data['requirements'] ?? null,
            $data['qualifications'] ?? null,
            $data['job_location'] ?? null,
            $data['job_type'] ?? null,
            $data['salary_range_min'] ?? null,
            $data['salary_range_max'] ?? null,
            $data['currency'] ?? null,
            $data['posted_date'] ?? null,
            $data['application_deadline'] ?? null,
            // post columns
            $data['post_title'] ?? null,
            $data['post_content'] ?? null,
            $data['views_count'] ?? null,
            $data['likes_count'] ?? null,
            $data['image'] ?? null,
            $data['is_published'] ?? null,
            $data['post_created_at'] ?? null,
            $data['post_updated_at'] ?? null,
        ];
        DB::connection('supabase')->insert($sql, $values);
    }

    public function processAllPosts()
    {
        $posts = Post::all();
        Log::info("Total posts to store: " . $posts->count());
        foreach ($posts as $post) {
            try {
                $data = [
                    'title' => $post->title,
                    'content' => $post->content,
                    'post_title' => $post->title,
                    'post_content' => $post->content,
                    'views_count' => $post->views_count,
                    'likes_count' => $post->likes_count,
                    'image' => $post->image,
                    'is_published' => $post->is_published,
                    'post_created_at' => $post->created_at,
                    'post_updated_at' => $post->updated_at,
                ];
                $this->storeInSupabase('post', $post->id, $data);
            } catch (\Throwable $e) {
                Log::error("Post failed (ID: {$post->id})", ['error' => $e->getMessage()]);
            }
        }
    }

    public function processAllJobs()
    {
        // إضافة فقط الوظائف المفتوحة
        $jobs = Job::where('status', 'Open')->get();
        Log::info("Total open jobs to store: " . $jobs->count());
        foreach ($jobs as $job) {
            try {
                $data = [
                    'title' => $job->job_title,
                    'content' => $job->description,
                    'job_title' => $job->job_title,
                    'description' => $job->description,
                    'requirements' => $job->requirements,
                    'qualifications' => $job->qualifications,
                    'job_location' => $job->job_location,
                    'job_type' => $job->job_type,
                    'salary_range_min' => $job->salary_range_min,
                    'salary_range_max' => $job->salary_range_max,
                    'currency' => $job->currency,
                    'posted_date' => $job->posted_date,
                    'application_deadline' => $job->application_deadline,
                ];
                $this->storeInSupabase('job', $job->id, $data);
            } catch (\Throwable $e) {
                Log::error("Job failed (ID: {$job->id})", ['error' => $e->getMessage()]);
            }
        }
    }

    public function searchInSupabase(string $query, int $limit = 1000)
    {
        // Full-text search on title/content/job_title/description/post_title/post_content
        $like = '%' . $query . '%';
        $sql = "
            SELECT * FROM itian_rag_knowledge
            WHERE (
                title ILIKE ? OR
                content ILIKE ? OR
                job_title ILIKE ? OR
                description ILIKE ? OR
                post_title ILIKE ? OR
                post_content ILIKE ?
            )
            ORDER BY posted_date DESC NULLS LAST, post_created_at DESC NULLS LAST
            LIMIT {$limit};
        ";
        $results = DB::connection('supabase')->select($sql, [$like, $like, $like, $like, $like, $like]);
        // إذا لم توجد نتائج، جرب البحث بكلمات مفردة
        if (empty($results)) {
            $words = preg_split('/\s+/', $query);
            $unionSql = [];
            $params = [];
            foreach ($words as $word) {
                $wlike = '%' . $word . '%';
                $unionSql[] = "SELECT * FROM itian_rag_knowledge WHERE (title ILIKE ? OR content ILIKE ? OR job_title ILIKE ? OR description ILIKE ? OR post_title ILIKE ? OR post_content ILIKE ?)";
                array_push($params, $wlike, $wlike, $wlike, $wlike, $wlike, $wlike);
            }
            if (count($unionSql) > 0) {
                $sql2 = implode(' UNION ', $unionSql) . " ORDER BY posted_date DESC NULLS LAST, post_created_at DESC NULLS LAST LIMIT {$limit}";
                $results = DB::connection('supabase')->select($sql2, $params);
            }
        }
        return $results;
    }

    public function askWithContext(string $query, $provider = 'gemini')
    {
        $results = $this->searchInSupabase($query, 5);
        if (empty($results)) {
            return $this->isArabic($query)
                ? 'عذراً، لا توجد معلومات متاحة للإجابة على هذا السؤال.'
                : "Sorry, we don't have any relevant information to answer this question.";
        }
        $context = collect($results)->map(function ($item) {
            $fields = [];
            if ($item->job_title) $fields[] = "Job Title: {$item->job_title}";
            if ($item->description) $fields[] = "Description: {$item->description}";
            if ($item->requirements) $fields[] = "Requirements: {$item->requirements}";
            if ($item->qualifications) $fields[] = "Qualifications: {$item->qualifications}";
            if ($item->job_location) $fields[] = "Location: {$item->job_location}";
            if ($item->job_type) $fields[] = "Type: {$item->job_type}";
            if ($item->salary_range_min || $item->salary_range_max) $fields[] = "Salary: {$item->salary_range_min} - {$item->salary_range_max} {$item->currency}";
            if ($item->posted_date) $fields[] = "Posted: {$item->posted_date}";
            if ($item->application_deadline) $fields[] = "Deadline: {$item->application_deadline}";
            if ($item->post_title) $fields[] = "Post Title: {$item->post_title}";
            if ($item->post_content) $fields[] = "Post Content: {$item->post_content}";
            if ($item->views_count !== null) $fields[] = "Views: {$item->views_count}";
            if ($item->likes_count !== null) $fields[] = "Likes: {$item->likes_count}";
            if ($item->image) $fields[] = "Image: {$item->image}";
            if ($item->is_published !== null) $fields[] = "Published: " . ($item->is_published ? 'Yes' : 'No');
            if ($item->post_created_at) $fields[] = "Created At: {$item->post_created_at}";
            if ($item->post_updated_at) $fields[] = "Updated At: {$item->post_updated_at}";
            if ($item->title) $fields[] = "Title: {$item->title}";
            if ($item->content) $fields[] = "Content: {$item->content}";
            return implode("\n", $fields);
        })->implode("\n\n");
        if ($this->isArabic($query)) {
            $prompt = "السؤال:\n{$query}\n\n" .
                      "كل المعلومات المتاحة من قاعدة البيانات:\n{$context}\n\n" .
                      "اعرض كل المعلومات كما هي بدون تلخيص أو حذف. إذا لم تجد الإجابة في المعلومات أعلاه، قل: عذراً، لا توجد معلومات متاحة للإجابة على هذا السؤال. لا تضف أي معلومة من عندك.";
        } else {
            $prompt = "Question:\n{$query}\n\n" .
                      "All available information from our database:\n{$context}\n\n" .
                      "Show ALL the information as-is, without summarizing or omitting anything. If the answer is not present, reply: Sorry, we don't have any relevant information to answer this question. Do NOT add any extra information.";
        }
        $apiKey = config('services.gemini.key');
        $endpoint = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-pro:generateContent";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-goog-api-key' => $apiKey,
        ])->post($endpoint, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);
        if ($response->failed()) {
            $errorBody = $response->json();
            \Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $errorBody,
            ]);
            $msg = $this->isArabic($query)
                ? 'حدث خطأ أثناء الاتصال بخدمة Gemini: ' . json_encode($errorBody, JSON_UNESCAPED_UNICODE)
                : 'Something went wrong while contacting Gemini: ' . json_encode($errorBody);
            return $msg;
        }
        return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    private function isArabic(string $text): bool
    {
        return preg_match('/\p{Arabic}/u', $text);
    }
}
