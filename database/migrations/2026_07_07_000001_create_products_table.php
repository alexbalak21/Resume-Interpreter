<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 80)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('product_unit');
            $table->integer('price'); // stored in cents, e.g. 1000 = €10.00
            $table->string('page_url');
            $table->timestamps(); // created_at + updated_at (replaces updated_on)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
