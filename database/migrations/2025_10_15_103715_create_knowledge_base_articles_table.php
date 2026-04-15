<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_base_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            
            // Categorization
            $table->string('category')->nullable();
            $table->json('tags')->nullable(); // ['hardware', 'printer', 'troubleshooting']
            
            // Author information (foreign key will be added later)
            $table->unsignedBigInteger('author_id');
            
            // Publishing
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            
            // Analytics
            $table->integer('views')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);
            
            // SEO
            $table->string('meta_description')->nullable();
            $table->json('related_articles')->nullable(); // [1, 2, 3] article IDs
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('category');
            $table->index('status');
            $table->index('author_id');
            $table->index('published_at');
            
            // Full-text search (only for MySQL/MariaDB)
            // SQLite doesn't support fulltext indexes
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['title', 'content']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_articles');
    }
};
