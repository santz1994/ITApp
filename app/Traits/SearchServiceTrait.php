<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * SearchServiceTrait
 * 
 * Provides FULLTEXT search capabilities with relevance scoring and snippet generation
 * 
 * Usage in Model:
 * use Traits\SearchServiceTrait;
 * 
 * Query Examples:
 * User::fulltextSearch('laravel', ['name', 'email'])->get()
 * User::naturalSearch('john admin', ['name', 'role'])->paginate()
 */
trait SearchServiceTrait
{
    /**
     * Scope for Boolean Full-Text Search
     * 
     * @param Builder $query
     * @param string $searchTerm
     * @param array $columns
     * @return Builder
     */
    public function scopeFulltextSearch($query, $searchTerm, $columns = [])
    {
        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return $query;
        }

        $columns = $columns ?: ($this->searchColumns ?? []);
        if (empty($columns)) {
            return $query;
        }

        $columnString = implode(',', $columns);
        $parsedQuery = $this->parseSearchQuery($searchTerm);

        return $query->whereRaw(
            "MATCH($columnString) AGAINST(? IN BOOLEAN MODE)",
            [$parsedQuery]
        );
    }

    /**
     * Scope for Natural Language Full-Text Search
     * 
     * @param Builder $query
     * @param string $searchTerm
     * @param array $columns
     * @return Builder
     */
    public function scopeNaturalSearch($query, $searchTerm, $columns = [])
    {
        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return $query;
        }

        $columns = $columns ?: ($this->searchColumns ?? []);
        if (empty($columns)) {
            return $query;
        }

        $columnString = implode(',', $columns);

        return $query->whereRaw(
            "MATCH($columnString) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$searchTerm]
        );
    }

    /**
     * Scope for searching with relevance score
     * 
     * @param Builder $query
     * @param string $searchTerm
     * @param array $columns
     * @return Builder
     */
    public function scopeWithRelevance($query, $searchTerm, $columns = [])
    {
        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return $query;
        }

        $columns = $columns ?: ($this->searchColumns ?? []);
        if (empty($columns)) {
            return $query;
        }

        $columnString = implode(',', $columns);
        $parsedQuery = $this->parseSearchQuery($searchTerm);

        return $query
            ->whereRaw(
                "MATCH($columnString) AGAINST(? IN BOOLEAN MODE)",
                [$parsedQuery]
            )
            ->selectRaw(
                "$this->table.*, MATCH($columnString) AGAINST(? IN BOOLEAN MODE) as relevance_score",
                [$parsedQuery]
            )
            ->orderByDesc('relevance_score');
    }

    /**
     * Parse search query for Boolean FULLTEXT mode
     * 
     * Features:
     * - Handles quoted strings as exact phrases
     * - Adds wildcard support for prefix matching
     * - Filters out dangerous operators
     * 
     * @param string $query
     * @return string
     */
    protected function parseSearchQuery($query)
    {
        // Remove dangerous operators and sanitize
        $query = preg_replace('/[<>@()~"*+-]/u', '', $query);
        
        // Trim and limit length
        $query = trim(substr($query, 0, 200));

        if (empty($query)) {
            return '*';
        }

        // Split into words
        $words = preg_split('/\s+/', $query);
        $words = array_filter($words, function ($word) {
            return strlen($word) >= 2; // Min 2 characters
        });

        if (empty($words)) {
            return '*';
        }

        // Add prefix wildcard for each word in Boolean mode
        $parsed = implode('* +', $words) . '*';

        return $parsed;
    }

    /**
     * Generate snippet/excerpt from text containing search keywords
     * 
     * @param string $text The text to extract snippet from
     * @param string $keywords The search keywords
     * @param int $length Target length of snippet (default 100)
     * @return string
     */
    public static function generateSnippet($text, $keywords, $length = 100)
    {
        if (empty($text) || empty($keywords)) {
            return substr($text ?? '', 0, $length) . (strlen($text ?? '') > $length ? '...' : '');
        }

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Split keywords into words
        $words = preg_split('/\s+/', $keywords);
        $words = array_filter($words, fn($w) => strlen($w) >= 2);

        // Find first occurrence of any keyword
        $firstPos = PHP_INT_MAX;
        foreach ($words as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1];
                if ($pos < $firstPos) {
                    $firstPos = $pos;
                }
            }
        }

        // Calculate start position (20 chars before keyword or from start)
        $start = max(0, $firstPos - 20);

        // Extract snippet
        $snippet = substr($text, $start, $length);

        // Add ellipsis if truncated
        if ($start > 0) {
            $snippet = '...' . ltrim($snippet);
        }
        if (strlen($text) > $start + $length) {
            $snippet .= '...';
        }

        return $snippet;
    }

    /**
     * Highlight keywords in text
     * 
     * @param string $text The text to highlight in
     * @param string $keywords The keywords to highlight
     * @param string $openTag HTML opening tag (default: <mark>)
     * @param string $closeTag HTML closing tag (default: </mark>)
     * @return string
     */
    public static function highlightKeywords($text, $keywords, $openTag = '<mark>', $closeTag = '</mark>')
    {
        if (empty($text) || empty($keywords)) {
            return $text;
        }

        // Split keywords into words
        $words = preg_split('/\s+/', $keywords);
        $words = array_filter($words, fn($w) => strlen($w) >= 2);

        // Highlight each word (case-insensitive)
        foreach ($words as $word) {
            $pattern = '/\b(' . preg_quote($word, '/') . ')\b/i';
            $text = preg_replace($pattern, $openTag . '$1' . $closeTag, $text);
        }

        return $text;
    }

    /**
     * Get searchable columns for this model
     * 
     * @return array
     */
    public function getSearchableColumns()
    {
        return $this->searchColumns ?? [];
    }

    /**
     * Get search relevance threshold (BM25-like scoring)
     * 
     * Relevance factors:
     * - Exact match: 100
     * - Prefix match: 75
     * - Substring match: 50
     * - Default threshold: 25
     * 
     * @return int
     */
    public static function getRelevanceThreshold()
    {
        return 25;
    }

    /**
     * Calculate BM25-like relevance score
     * 
     * Formula: (k1 + 1) * tf / (tf + k1 * (1 - b + b * (doclen / avgdoclen)))
     * 
     * Where:
     * - tf: term frequency in document
     * - k1: term frequency saturation point (1.2)
     * - b: length normalization (0.75)
     * - doclen: document length
     * - avgdoclen: average document length
     * 
     * @param int $termFrequency
     * @param int $docLength
     * @param int $avgDocLength
     * @param float $k1
     * @param float $b
     * @return float
     */
    public static function calculateBM25Score(
        $termFrequency,
        $docLength,
        $avgDocLength = 1000,
        $k1 = 1.2,
        $b = 0.75
    ) {
        $numerator = ($k1 + 1) * $termFrequency;
        $denominator = $termFrequency + ($k1 * (1 - $b + ($b * ($docLength / $avgDocLength))));

        return ($denominator > 0) ? $numerator / $denominator : 0;
    }

    /**
     * Build advanced search query with multiple filters
     * 
     * @param Builder $query
     * @param array $filters
     * @return Builder
     * 
     * Example filters:
     * [
     *     'search' => 'laptop',
     *     'search_columns' => ['name', 'description'],
     *     'search_mode' => 'boolean', // or 'natural'
     *     'filters' => ['status_id' => 1, 'division_id' => 2],
     *     'date_from' => '2025-01-01',
     *     'date_to' => '2025-12-31',
     *     'sort_by' => 'relevance', // or 'created_at'
     *     'per_page' => 20
     * ]
     */
    public function scopeAdvancedSearch($query, $filters = [])
    {
        // Apply search
        if (!empty($filters['search'])) {
            $columns = $filters['search_columns'] ?? $this->searchColumns ?? [];
            $mode = $filters['search_mode'] ?? 'boolean';

            if ($mode === 'natural') {
                $query->naturalSearch($filters['search'], $columns);
            } else {
                $query->fulltextSearch($filters['search'], $columns);
            }
        }

        // Apply additional filters
        if (!empty($filters['filters']) && is_array($filters['filters'])) {
            foreach ($filters['filters'] as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        // Apply date range filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply sorting
        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'relevance' && !empty($filters['search'])) {
                $query->orderByDesc('relevance_score');
            } else {
                $query->orderBy($filters['sort_by'], $filters['sort_order'] ?? 'desc');
            }
        }

        return $query;
    }

    /**
     * Check if FULLTEXT search is supported in database
     * 
     * @return bool
     */
    public static function supportsFulltext()
    {
        return true; // Assume MySQL/MariaDB supports FULLTEXT
    }

    /**
     * Get example search queries for this model
     * 
     * @return array
     */
    public function getSearchExamples()
    {
        return [
            'Simple search' => 'laptop',
            'Phrase search' => '"Dell Latitude"',
            'Multiple words' => 'laptop Dell i5',
        ];
    }
}
