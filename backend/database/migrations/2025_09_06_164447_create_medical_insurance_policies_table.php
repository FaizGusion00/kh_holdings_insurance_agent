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
        Schema::create('medical_insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('medical_insurance_registrations')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('medical_insurance_plans')->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->string('policy_number')->unique();
            $table->string('customer_type'); // primary, second, third
            $table->string('customer_name');
            $table->string('customer_nric', 14);
            $table->string('customer_phone', 20);
            $table->string('customer_email');
            $table->string('payment_frequency'); // monthly, quarterly, half_yearly, yearly
            $table->decimal('premium_amount', 10, 2);
            $table->decimal('commitment_fee', 10, 2)->default(0);
            $table->string('medical_card_type');
            $table->enum('status', ['active', 'suspended', 'cancelled', 'expired'])->default('active');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('next_payment_date')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_insurance_policies');
    }
};
