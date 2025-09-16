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
        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_plan_id')->constrained()->onDelete('cascade');
            $table->enum('payment_mode', ['monthly', 'quarterly', 'semi_annually', 'annually']);
            $table->integer('tier_level'); // T1, T2, T3, T4, T5 (1-5)
            $table->decimal('commission_percentage', 5, 2); // e.g., 11.11, 2.22, etc.
            $table->decimal('commission_amount', 10, 2); // Calculated amount based on plan price
            $table->timestamps();
            
            // Indexes
            $table->index(['insurance_plan_id', 'payment_mode', 'tier_level']);
            $table->unique(['insurance_plan_id', 'payment_mode', 'tier_level'], 'commission_rates_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rates');
    }
};
