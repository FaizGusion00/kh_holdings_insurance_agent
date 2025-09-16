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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('insurance_plan_id')->constrained()->onDelete('cascade');
            $table->string('policy_number')->unique();
            $table->enum('payment_mode', ['monthly', 'quarterly', 'semi_annually', 'annually']);
            $table->decimal('premium_amount', 10, 2); // The amount paid for this policy
            $table->date('policy_start_date');
            $table->date('policy_end_date');
            $table->date('next_payment_due')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended', 'pending_payment'])->default('pending_payment');
            $table->integer('payment_count')->default(0); // How many payments made
            $table->decimal('total_paid', 15, 2)->default(0); // Total amount paid for this policy
            $table->boolean('auto_renewal')->default(true);
            $table->text('policy_documents')->nullable(); // JSON array of document URLs
            $table->text('beneficiaries')->nullable(); // JSON array of beneficiaries
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('insurance_plan_id');
            $table->index('policy_number');
            $table->index('status');
            $table->index('policy_end_date');
            $table->index('next_payment_due');
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
