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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')
                  ->references('id')->on('sellers')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Or 'set null' if categories can exist without a seller
            $table->string('name');
            $table->string('slug');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')
                  ->references('id')->on('product_categories')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            $table->timestamps();

            // Unique constraint on slug combined with seller_id for categories
            $table->unique(['slug', 'seller_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};

