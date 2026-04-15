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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'email_enabled', 'whatsapp_enabled'
            $table->text('value')->nullable(); // JSON or string value
            $table->string('category')->default('general'); // 'email', 'whatsapp', 'telegram', 'general'
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('notification_settings')->insert([
            // Email Settings
            [
                'key' => 'email_enabled',
                'value' => 'true',
                'category' => 'email',
                'description' => 'Enable/disable email notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_meeting_approval',
                'value' => 'true',
                'category' => 'email',
                'description' => 'Send email when meeting room is approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_meeting_rejection',
                'value' => 'true',
                'category' => 'email',
                'description' => 'Send email when meeting room is rejected',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_ticket_created',
                'value' => 'true',
                'category' => 'email',
                'description' => 'Send email when ticket is created',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_ticket_assigned',
                'value' => 'true',
                'category' => 'email',
                'description' => 'Send email when ticket is assigned',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_ticket_updated',
                'value' => 'true',
                'category' => 'email',
                'description' => 'Send email when ticket status is updated',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // WhatsApp Settings
            [
                'key' => 'whatsapp_enabled',
                'value' => 'false',
                'category' => 'whatsapp',
                'description' => 'Enable/disable WhatsApp notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'whatsapp_api_url',
                'value' => '',
                'category' => 'whatsapp',
                'description' => 'WhatsApp API URL endpoint',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'whatsapp_api_token',
                'value' => '',
                'category' => 'whatsapp',
                'description' => 'WhatsApp API authentication token',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Telegram Settings
            [
                'key' => 'telegram_enabled',
                'value' => 'false',
                'category' => 'telegram',
                'description' => 'Enable/disable Telegram notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'telegram_bot_token',
                'value' => '',
                'category' => 'telegram',
                'description' => 'Telegram Bot API token',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'telegram_chat_id',
                'value' => '',
                'category' => 'telegram',
                'description' => 'Default Telegram chat/group ID',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
