<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('requested_by')->unsigned();
            $table->integer('asset_type_id')->unsigned()->nullable();
            $table->text('justification');
            $table->string('status')->default('pending');
            $table->integer('approved_by')->unsigned()->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->integer('fulfilled_asset_id')->unsigned()->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
            
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('asset_type_id')->references('id')->on('asset_types')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('fulfilled_asset_id')->references('id')->on('assets')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_requests');
    }
};