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
        Schema::create('canvas_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('width', 8, 2);
            $table->decimal('height', 8, 2);
            $table->string('unit')->default('cm');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canvas_sizes');
    }
};
