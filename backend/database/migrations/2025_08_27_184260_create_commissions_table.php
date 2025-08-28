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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('referrer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('product_id')->constrained('insurance_products')->onDelete('cascade');
            $table->foreignId('policy_id')->nullable()->constrained('member_policies')->onDelete('cascade');
            $table->tinyInteger('tier_level');
            $table->enum('commission_type', ['direct', 'indirect', 'bonus']);
            $table->decimal('base_amount', 15, 2);
            $table->decimal('commission_percentage', 10, 4);
            $table->decimal('commission_amount', 15, 2);
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'semi_annually', 'annually']);
            $table->tinyInteger('month');
            $table->year('year');
            $table->enum('status', ['pending', 'calculated', 'paid'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'status', 'year', 'month']);
            $table->index(['product_id', 'tier_level']);
            $table->index(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
