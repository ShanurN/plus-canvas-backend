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
        Schema::create('canvas_format_size', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canvas_format_id')->constrained()->onDelete('cascade');
            $table->foreignId('canvas_size_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canvas_format_size');
    }
};
