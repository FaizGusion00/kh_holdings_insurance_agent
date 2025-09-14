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
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name'); // e.g., "Senior Care Plan Gold 270", "Medical Card"
            $table->string('plan_type'); // e.g., "senior_care", "medical_card"
            $table->string('payment_frequency')->nullable(); // monthly, quarterly, semi_annually, annually
            $table->decimal('base_amount', 10, 2); // Base amount for calculation
            $table->integer('tier_level'); // T1, T2, T3, T4, T5
            $table->decimal('commission_percentage', 5, 2)->nullable(); // For percentage-based commissions
            $table->decimal('commission_amount', 10, 2)->nullable(); // For fixed amount commissions
            $table->string('commission_type'); // percentage, fixed_amount
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['plan_name', 'payment_frequency', 'tier_level']);
            $table->index(['plan_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
