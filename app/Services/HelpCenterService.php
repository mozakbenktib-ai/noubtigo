<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HelpCenterService
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = resource_path('help');
    }

    /**
     * Get all articles for a given locale, optionally filtered by role
     */
    public function getArticles(string $locale = 'en', ?string $role = null): array
    {
        $langDir = $this->basePath . '/' . $locale;
        if (!File::exists($langDir)) {
            $langDir = $this->basePath . '/en';
        }

        if (!File::exists($langDir)) {
            return [];
        }

        $articles = [];
        $files = File::allFiles($langDir);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $parsed = $this->parseMarkdownFile($file->getRealPath());
            if (!$parsed) {
                continue;
            }

            // Role filtering check if role provided
            if ($role && !empty($parsed['roles'])) {
                if (!in_array($role, $parsed['roles']) && !in_array('admin', $parsed['roles'])) {
                    // Skip if role doesn't match
                    continue;
                }
            }

            $articles[] = $parsed;
        }

        return $articles;
    }

    /**
     * Get categories with grouped articles
     */
    public function getCategoriesWithArticles(string $locale = 'en', ?string $role = null): array
    {
        $articles = $this->getArticles($locale, $role);
        $categories = [];

        foreach ($articles as $article) {
            $cat = $article['category'] ?? 'General';
            if (!isset($categories[$cat])) {
                $categories[$cat] = [
                    'name' => $cat,
                    'slug' => Str::slug($cat),
                    'articles' => []
                ];
            }
            $categories[$cat]['articles'][] = $article;
        }

        return $categories;
    }

    /**
     * Find a single article by category slug & article slug
     */
    public function getArticle(string $categorySlug, string $articleSlug, string $locale = 'en', ?string $role = null): ?array
    {
        $articles = $this->getArticles($locale, $role);
        foreach ($articles as $article) {
            if (Str::slug($article['category']) === $categorySlug && $article['slug'] === $articleSlug) {
                return $article;
            }
        }
        
        // Fallback to English if not found in requested locale
        if ($locale !== 'en') {
            return $this->getArticle($categorySlug, $articleSlug, 'en', $role);
        }

        return null;
    }

    /**
     * Get contextual article matching current route name
     */
    public function getContextualArticle(string $routeName, string $locale = 'en', ?string $role = null): ?array
    {
        $articles = $this->getArticles($locale, $role);

        foreach ($articles as $article) {
            if (!empty($article['routes']) && in_array($routeName, $article['routes'])) {
                return $article;
            }
        }

        // Default onboarding fallback if no specific match
        return $this->getArticle('getting-started', 'onboarding', $locale);
    }

    /**
     * Search articles by keyword query
     */
    public function search(string $query, string $locale = 'en', ?string $role = null): array
    {
        if (trim($query) === '') {
            return [];
        }

        $articles = $this->getArticles($locale, $role);
        $queryLower = mb_strtolower($query);

        return array_values(array_filter($articles, function ($article) use ($queryLower) {
            $titleMatch = mb_strpos(mb_strtolower($article['title']), $queryLower) !== false;
            $descMatch = mb_strpos(mb_strtolower($article['description']), $queryLower) !== false;
            $catMatch = mb_strpos(mb_strtolower($article['category']), $queryLower) !== false;
            $contentMatch = mb_strpos(mb_strtolower($article['content_raw']), $queryLower) !== false;

            return $titleMatch || $descMatch || $catMatch || $contentMatch;
        }));
    }

    /**
     * Parse Front-Matter metadata & Markdown body from file
     */
    protected function parseMarkdownFile(string $filePath): ?array
    {
        $content = File::get($filePath);
        $filename = pathinfo($filePath, PATHINFO_FILENAME);

        $pattern = '/^---\s*\n(.*?)\n---\s*\n(.*)/s';
        if (!preg_match($pattern, $content, $matches)) {
            return [
                'slug' => Str::slug($filename),
                'title' => Str::title(str_replace('-', ' ', $filename)),
                'category' => 'General',
                'description' => '',
                'roles' => ['admin', 'secretary', 'staff', 'customer'],
                'routes' => [],
                'video_url' => null,
                'content_raw' => $bodyRaw,
                'content_html' => Str::markdown($bodyRaw, ['html_input' => 'allow', 'allow_unsafe_links' => false])
            ];
        }

        $frontMatterRaw = $matches[1];
        $bodyRaw = $matches[2];

        $metadata = [];
        foreach (explode("\n", $frontMatterRaw) as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            if (strpos($line, ':') !== false) {
                list($key, $val) = explode(':', $line, 2);
                $key = trim($key);
                $val = trim($val);

                // Parse array syntax [admin, secretary]
                if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
                    $items = explode(',', trim($val, '[]'));
                    $val = array_map(fn($i) => trim($i, " '\""), $items);
                } else {
                    $val = trim($val, " '\"");
                }

                $metadata[$key] = $val;
            }
        }

        $category = $metadata['category'] ?? 'General';

        return [
            'slug' => Str::slug($metadata['slug'] ?? $filename),
            'title' => $metadata['title'] ?? Str::title(str_replace('-', ' ', $filename)),
            'category' => $category,
            'category_slug' => Str::slug($category),
            'description' => $metadata['description'] ?? '',
            'roles' => (array)($metadata['roles'] ?? ['admin', 'secretary', 'staff', 'customer']),
            'routes' => (array)($metadata['routes'] ?? []),
            'video_url' => $metadata['video_url'] ?? null,
            'content_raw' => $bodyRaw,
            'content_html' => Str::markdown($bodyRaw, ['html_input' => 'allow', 'allow_unsafe_links' => false])
        ];
    }
}
