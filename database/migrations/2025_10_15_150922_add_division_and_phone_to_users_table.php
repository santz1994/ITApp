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
        Schema::table('users', function (Blueprint $table) {
            // Add division_id column if it doesn't exist
            if (!Schema::hasColumn('users', 'division_id')) {
                $table->integer('division_id')->unsigned()->nullable()->after('email');
                $table->foreign('division_id')->references('id')->on('divisions')->onDelete('set null');
            }
            
            // Add phone column if it doesn't exist
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('division_id');
            }
            
            // Add is_active column if it doesn't exist
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('phone');
            }
            
            // Add last_login_at column if it doesn't exist
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('users', 'division_id')) {
                $table->dropForeign(['division_id']);
                $table->dropColumn('division_id');
            }
            
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
        });
    }
};
