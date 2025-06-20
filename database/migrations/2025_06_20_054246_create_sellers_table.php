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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')
                  ->references('id')->on('marketplace_users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->string('shop_name')->unique();
            $table->string('shop_url')->nullable();
            $table->text('shop_description')->nullable();
            $table->string('client_id')->unique();
            $table->string('client_secret');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
