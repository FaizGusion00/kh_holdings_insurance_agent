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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('agent_code')->unique()->nullable(); // For agents to refer new users
            $table->string('referrer_code')->nullable(); // Agent code of the person who referred this user
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('nric')->unique();
            $table->enum('race', ['Malay', 'Chinese', 'Indian', 'Other']);
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('occupation');
            $table->integer('height_cm')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relationship');
            $table->boolean('medical_consultation_2_years')->default(false);
            $table->text('serious_illness_history')->nullable();
            $table->boolean('insurance_rejection_history')->default(false);
            $table->text('serious_injury_history')->nullable();
            $table->timestamp('registration_date')->useCurrent();
            $table->string('relationship_with_agent')->nullable();
            $table->decimal('balance', 15, 2)->default(0); // Commission balance
            $table->decimal('wallet_balance', 15, 2)->default(0); // Virtual wallet balance
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_owner')->nullable();
            $table->integer('mlm_level')->default(1); // MLM hierarchy level
            $table->decimal('total_commission_earned', 15, 2)->default(0);
            $table->decimal('monthly_commission_target', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending_verification'])->default('pending_verification');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('mlm_activation_date')->nullable();
            $table->string('plan_name')->nullable(); // Current insurance plan
            $table->enum('payment_mode', ['monthly', 'quarterly', 'semi_annually', 'annually'])->nullable();
            $table->string('medical_card_type')->nullable();
            $table->enum('customer_type', ['client', 'agent'])->default('client');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('agent_code');
            $table->index('referrer_code');
            $table->index('email');
            $table->index('nric');
            $table->index('status');
            $table->index('customer_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
