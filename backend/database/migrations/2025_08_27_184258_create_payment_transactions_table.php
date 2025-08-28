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
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('policy_id')->nullable()->constrained('member_policies')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', ['membership_fee', 'sharing_account', 'policy_premium']);
            $table->enum('payment_method', ['mandate', 'manual', 'card']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->datetime('transaction_date');
            $table->text('description')->nullable();
            $table->string('reference_number')->unique();
            $table->string('gateway_reference')->nullable();
            $table->json('gateway_response')->nullable();
            $table->datetime('processed_at')->nullable();
            $table->datetime('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['policy_id', 'status']);
            $table->index(['transaction_date']);
            $table->index(['payment_type']);
            $table->index(['reference_number']);
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
