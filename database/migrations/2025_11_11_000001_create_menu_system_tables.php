<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuSystemTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table: menus - Store all menu items
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // Menu display text
            $table->string('route')->nullable(); // Laravel route name
            $table->string('url')->nullable(); // Direct URL (for external links)
            $table->string('icon', 100)->nullable(); // FontAwesome icon class
            $table->unsignedBigInteger('parent_id')->nullable(); // For submenu/hierarchical structure
            $table->integer('order_index')->default(0); // Display order
            $table->boolean('is_active')->default(true); // Enable/disable menu
            $table->boolean('is_external')->default(false); // External link?
            $table->string('target', 50)->default('_self'); // Link target (_self, _blank)
            $table->string('css_class')->nullable(); // Additional CSS classes
            $table->text('description')->nullable(); // Menu description/tooltip
            $table->timestamps();
            
            // Indexes for performance
            $table->index('parent_id');
            $table->index(['is_active', 'order_index']);
        });

        // Table: menu_role - Permission matrix (which roles can see which menus)
        Schema::create('menu_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('role_id');
            $table->boolean('can_view')->default(true);
            $table->timestamps();
            
            // Unique constraint - one entry per menu-role combination
            $table->unique(['menu_id', 'role_id']);
            
            // Indexes for performance
            $table->index('menu_id');
            $table->index('role_id');
        });

        // Optional Table: menu_user - User-specific overrides (if needed)
        // This allows Super Admin to grant/revoke menu access for individual users
        Schema::create('menu_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('can_view')->default(true);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['menu_id', 'user_id']);
            
            // Indexes for performance
            $table->index('menu_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_user');
        Schema::dropIfExists('menu_role');
        Schema::dropIfExists('menus');
    }
}
