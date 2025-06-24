<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // Text field
            $table->text('description')->nullable(); // Text field
            $table->string('image')->nullable(); // Image field as string path
            $table->decimal('price', 10, 2); // Numeric field
            $table->unsignedBigInteger('category_id'); // Numeric field (and likely a foreign key)
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('status'); // Text field
            $table->string('arrival_status')->default('regular');
            $table->string('cost_price'); // Text field
            $table->integer('stock_quantity')->default(1); // Text field
            $table->integer('sales')->default(0); // Text field
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
