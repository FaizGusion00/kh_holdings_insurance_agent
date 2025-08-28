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
        Schema::create('member_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('insurance_products')->onDelete('cascade');
            $table->string('policy_number')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active');
            $table->decimal('monthly_premium', 10, 2);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->date('next_payment_date');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['member_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index('next_payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_policies');
    }
};
