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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();

            // Auditable
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->index(['auditable_type', 'auditable_id']);

            // Action
            $table->string('action'); // created, updated, deleted, restored, etc.
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_attributes')->nullable(); // List of changed fields

            // User/Context
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();

            // Additional
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // No soft deletes - audit logs are immutable

            $table->index(['tenant_id', 'auditable_type', 'auditable_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'action']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
