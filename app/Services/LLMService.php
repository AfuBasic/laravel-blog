<?php

// app/Services/OllamaService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use LucianoTonet\GroqLaravel\Facades\Groq;

use function PHPUnit\Framework\matches;

class LLMService
{
    public function generateBlogContent(string $category): array
    {
        $prompt = <<<PROMPT
You are an expert blog writer and SEO specialist who writes relatable, engaging, and informative blog posts for a wide audience.

Write a complete blog post about the topic: "$category".

Guidelines:
- Use a conversational tone that feels personal, warm, and clear.
- Start with a relatable introduction that hooks the reader.
- Break the article into logical sections with clear subheadings.
- Include real-life examples, tips, or analogies where possible.
- Write with empathy — make it accessible for readers with no technical background.
- Keep the language simple, yet insightful.
- The article should be 800–1500 words long.
- Add a compelling conclusion with a friendly call to action (CTA).

Target audience:
- General readers aged 18–40.
- Interested in lifestyle, self-improvement, tech, health, or practical knowledge depending on the category.

Formatting Instructions (Important!):
- Format the **entire blog post in HTML**.
- Wrap the blog post title in a `<h1>` tag.
- Use `<h2>` and `<h3>` for section headings and subheadings.
- Wrap all text content in `<p>` tags.
- Use `<strong>` for emphasis, tips, and analogies.
- Use `<ul>` and `<li>` for bullet points.
- Add `<br>` between major sections for better readability.
- Do NOT use asterisks, markdown, or quotation symbols for formatting.

Do not include explanations. Just output the raw HTML blog post content.

PROMPT;


        $response = Groq::chat()->completions()->create([
            'model' => env('GROQ_MODEL'), // Or any other model
            'messages' => [
                ['role' => 'system', 'content' => 'You are a blog content writer'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);


        $blogpost = $response['choices'][0]['message']['content'];

        $title = $this->extractBlogTitle($blogpost);
        $content = $this->extractContentWithoutTitle($blogpost);

        return [
            'title' => $title,
            'content' => $content,
        ];
    }

    public function extractBlogTitle(string $htmlContent)
    {
        // Fix possible invalid starting </h1> tags
        $htmlContent = preg_replace('/^<\/h1>/', '<h1>', trim($htmlContent));

        // Match a valid <h1>Title</h1>
        if (preg_match('/<h1>(.*?)<\/h1>/is', $htmlContent, $matches)) {
            return trim($matches[1]);
        }
        // If no h1 found, fallback or return default
        return 'Title could not be extracted.';
    }

    public function extractContentWithoutTitle(string $htmlContent): string
    {
        // Fix malformed starting tag
        $htmlContent = preg_replace('/^<\/h1>/', '<h1>', trim($htmlContent));

        // Remove the first <h1>...</h1> block
        $htmlContent = preg_replace('/<h1>.*?<\/h1>/is', '', $htmlContent, 1);

        // Optionally trim and return
        return trim($htmlContent);
    }
}
