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
        // Hash existing plain text API tokens
        $users = \App\User::whereNotNull('api_token')->get();
        
        foreach ($users as $user) {
            // Only hash if the token is not already hashed
            if (!password_verify('test', $user->api_token) && strlen($user->api_token) < 60) {
                $hashedToken = hash('sha256', $user->api_token);
                $user->update(['api_token' => $hashedToken]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse hashing, but we can comment this for documentation
        // Original plain text tokens were hashed in up() method
        // This is irreversible for security reasons
    }
};
