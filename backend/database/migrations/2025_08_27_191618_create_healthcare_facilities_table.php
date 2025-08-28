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
        Schema::create('healthcare_facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['hospital', 'clinic']);
            $table->text('address')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_panel_facility')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('healthcare_facilities');
    }
};
