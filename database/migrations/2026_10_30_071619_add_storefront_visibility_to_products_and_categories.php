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
        // Products table - add storefront visibility
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('is_featured');
            $table->timestamp('published_at')->nullable()->after('is_published');
        });

        // Categories table - add storefront visibility
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_storefront_visible')->default(true)->after('is_service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'published_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['is_storefront_visible']);
        });
    }
};
