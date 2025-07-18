<?php

// app/Services/OllamaService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

use function PHPUnit\Framework\matches;

class LLMService
{
    public function generateBlogContent(string $category): array
    {
        $prompt = <<<PROMPT
You are an expert blog writer. Generate a well-formatted HTML blog post for the category "{$category}". The blog must include:

- A **Title**, clearly marked like this: Title: <your-title>
- A **Content** section with valid HTML structure, marked like this: Content: <html>

The content should include headings, paragraphs, and lists using standard HTML tags (e.g. <h2>, <p>, <ul>). Do not include markdown or extra explanations.

Respond only in this format:

Title: <your-title>

Content:
<your-html>
PROMPT;

        $response = Http::timeout(30)->post('http://localhost:11434/api/generate', [
            'model' => 'llama3.1',
            'prompt' => $prompt,
            'stream' => false,
        ]);

        $text = $response->json('response');

        // Simple parsing — feel free to refine this
        if (!is_string($text)) {
            return [
                'title' => 'Untitled',
                'content' => 'No content generated. [Invalid response format]',
            ];
        }

        // Strict match on "Title:" and "Content:"
        if (preg_match('/Title:\s*(.*?)\s*Content:\s*(.+)/is', $text, $matches)) {
            return [
                'title' => trim($matches[1]),
                'content' => trim($matches[2]),
            ];
        }
        return [
            'title' => 'Untitled',
            'content' => 'No content generated. [Could not parse response]',
        ];
    }
}
