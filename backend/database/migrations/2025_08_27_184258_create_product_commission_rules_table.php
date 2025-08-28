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
        Schema::create('product_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('insurance_products')->onDelete('cascade');
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'semi_annually', 'annually']);
            $table->tinyInteger('tier_level');
            $table->enum('commission_type', ['percentage', 'fixed_amount']);
            $table->decimal('commission_value', 10, 4);
            $table->decimal('minimum_requirement', 10, 2)->default(0);
            $table->decimal('maximum_cap', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['product_id', 'tier_level', 'is_active']);
            $table->index('payment_frequency');
            // Use shorter name to avoid MySQL identifier length limit (64 chars)
            $table->unique(['product_id', 'payment_frequency', 'tier_level'], 'pc_rules_prod_freq_tier_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_commission_rules');
    }
};
