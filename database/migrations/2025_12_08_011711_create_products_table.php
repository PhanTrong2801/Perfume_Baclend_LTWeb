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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Unisex']);
            $table->string('thumbnail')->nullable();
            
            // Khóa ngoại
            $table->foreignId('brand_id')->constrained('brands', 'brand_id')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories', 'category_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
