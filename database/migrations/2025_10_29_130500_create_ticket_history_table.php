<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ticket_history')) {
            Schema::create('ticket_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('ticket_id')->index();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->string('event_type')->index();
                $table->json('data')->nullable();
                $table->timestamps();

                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ticket_history');
    }
};
