<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 10)->index();
            $table->enum('side', ['buy', 'sell']);
            $table->decimal('price', 18, 8);
            $table->decimal('amount', 18, 8);
            $table->decimal('locked_usd', 18, 8)->default(0);
            $table->unsignedTinyInteger('status')->default(1)->index(); // 1=open, 2=filled, 3=cancelled
            $table->timestamps();
            $table->index(['symbol', 'side', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
