<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KnowledgeBaseArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'category',
        'tags',
        'author_id',
        'status',
        'published_at',
        'views',
        'helpful_count',
        'not_helpful_count',
        'meta_description',
        'related_articles',
    ];

    protected $casts = [
        'tags' => 'array',
        'related_articles' => 'array',
        'published_at' => 'datetime',
        'views' => 'integer',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title if not provided
        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    /**
     * Get the author of the article
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope to get only published articles
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope to filter by category
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to search articles
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhereJsonContains('tags', $search);
        });
    }

    /**
     * Increment view count
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Mark as helpful
     */
    public function markHelpful()
    {
        $this->increment('helpful_count');
    }

    /**
     * Mark as not helpful
     */
    public function markNotHelpful()
    {
        $this->increment('not_helpful_count');
    }

    /**
     * Get helpfulness percentage
     */
    public function getHelpfulnessPercentageAttribute()
    {
        $total = $this->helpful_count + $this->not_helpful_count;
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($this->helpful_count / $total) * 100, 2);
    }
}
