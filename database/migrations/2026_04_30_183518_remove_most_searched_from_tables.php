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
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'is_most_searched')) {
                $table->dropColumn('is_most_searched');
            }
            if (Schema::hasColumn('categories', 'most_searched_order')) {
                $table->dropColumn('most_searched_order');
            }
        });

        Schema::table('brands', function (Blueprint $table) {
            if (Schema::hasColumn('brands', 'is_most_searched')) {
                $table->dropColumn('is_most_searched');
            }
            if (Schema::hasColumn('brands', 'most_searched_order')) {
                $table->dropColumn('most_searched_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_most_searched')->default(false);
            $table->integer('most_searched_order')->default(0);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->boolean('is_most_searched')->default(false);
            $table->integer('most_searched_order')->default(0);
        });
    }
};
