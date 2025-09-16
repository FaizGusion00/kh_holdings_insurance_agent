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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_policy_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_id')->unique(); // Curlec or internal transaction ID
            $table->string('gateway_transaction_id')->nullable(); // Payment gateway reference
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('MYR');
            $table->enum('payment_method', ['curlec', 'fpx', 'credit_card', 'debit_card', 'ewallet', 'bank_transfer']);
            $table->enum('payment_type', ['premium', 'commitment_fee', 'renewal', 'top_up'])->default('premium');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->text('gateway_response')->nullable(); // JSON response from payment gateway
            $table->string('receipt_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('member_policy_id');
            $table->index('transaction_id');
            $table->index('gateway_transaction_id');
            $table->index('status');
            $table->index('payment_type');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
