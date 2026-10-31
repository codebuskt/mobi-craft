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
        Schema::table('customers', function (Blueprint $table) {
            // Storefront authentication fields
            $table->string('password')->nullable()->after('notes');
            $table->timestamp('email_verified_at')->nullable()->after('password');
            $table->string('remember_token', 100)->nullable()->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');

            // Storefront preferences
            $table->json('storefront_preferences')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'email_verified_at',
                'remember_token',
                'last_login_at',
                'storefront_preferences',
            ]);
        });
    }
};
