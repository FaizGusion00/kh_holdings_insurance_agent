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
        Schema::create('medical_insurance_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // MediPlan Coop, Senior Care Plan Gold 270, Senior Care Plan Diamond 370
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('quarterly_price', 10, 2)->nullable();
            $table->decimal('half_yearly_price', 10, 2)->nullable();
            $table->decimal('yearly_price', 10, 2);
            $table->decimal('commitment_fee', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('coverage_details')->nullable();
            $table->integer('max_age')->nullable();
            $table->integer('min_age')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_insurance_plans');
    }
};
