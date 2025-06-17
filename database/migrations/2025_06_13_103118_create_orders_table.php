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
            $table->id();
            $table->string('uniq_id');
            $table->unsignedBigInteger('customer_id')->nullable(); // Must be nullable for onDelete('set null')
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->string('type')->nullable();
            $table->integer('items')->default(1);
            $table->string('status')->default('pending');
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_price', 10, 2)->default(0);
            $table->longText('order_summary')->nullable(); // assuming it's a structured summary
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->unsignedBigInteger('promocode_id')->nullable(); // Must be nullable for onDelete('set null')
            $table->foreign('promocode_id')->references('id')->on('promo_codes')->onDelete('set null');
            $table->string('promocode_name')->nullable();
            $table->decimal('total');
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
