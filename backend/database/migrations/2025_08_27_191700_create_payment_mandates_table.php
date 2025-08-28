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
        Schema::create('payment_mandates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('policy_id')->nullable()->constrained('member_policies')->onDelete('cascade');
            $table->enum('mandate_type', ['membership_fee', 'sharing_account', 'recurring']);
            $table->enum('frequency', ['monthly', 'quarterly', 'half_yearly', 'yearly']);
            $table->decimal('amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('bank_account', 50);
            $table->string('bank_name', 100);
            $table->enum('status', ['active', 'inactive', 'cancelled', 'suspended'])->default('active');
            $table->string('reference_number')->unique();
            $table->string('gateway_reference')->nullable();
            $table->json('gateway_response')->nullable();
            $table->datetime('last_processed_at')->nullable();
            $table->date('next_processing_date');
            $table->integer('total_processed')->default(0);
            $table->decimal('total_amount_processed', 10, 2)->default(0.00);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['member_id']);
            $table->index(['policy_id']);
            $table->index(['mandate_type']);
            $table->index(['next_processing_date']);
            $table->index(['reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_mandates');
    }
};
