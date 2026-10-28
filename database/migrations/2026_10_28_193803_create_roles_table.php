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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // super_admin, shop_owner, shop_manager, cashier, technician, inventory_manager, accountant
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // System roles cannot be deleted
            $table->boolean('is_tenant_scoped')->default(true); // true for tenant roles, false for super_admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
