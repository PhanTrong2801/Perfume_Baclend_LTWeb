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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->foreignId('user_id')->constrained('users','user_id')->onDelete('cascade');
            
            $table->dateTime('order_date')->useCurrent();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'])->default('Pending');
            $table->text('shipping_address');
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
