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
        Schema::create('insurance_products', function (Blueprint $table) {
            $table->id();
            $table->enum('product_type', ['medical_card', 'roadtax', 'hibah', 'travel_pa']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'semi_annually', 'annually'])->default('monthly');
            $table->decimal('price_multiplier', 5, 2)->default(1.00);
            $table->json('coverage_details')->nullable();
            $table->integer('waiting_period_days')->default(0);
            $table->decimal('max_coverage_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['product_type', 'is_active']);
            $table->index('payment_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_products');
    }
};
