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
        Schema::create('medical_insurance_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->string('registration_number')->unique();
            
            // Primary customer information
            $table->string('agent_code');
            $table->string('plan_type'); // Mediplan Coop, Senior Care Plan Gold 270, Senior Care Plan Diamond 370
            $table->string('full_name');
            $table->string('nric', 14);
            $table->string('race');
            $table->integer('height_cm');
            $table->integer('weight_kg');
            $table->string('phone_number', 20);
            $table->string('email');
            $table->string('password');
            
            // Medical history questions
            $table->boolean('medical_consultation_2_years');
            $table->boolean('serious_illness_history');
            $table->boolean('insurance_rejection_history');
            $table->boolean('serious_injury_history');
            
            // Emergency contact
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone', 20);
            $table->string('emergency_contact_relationship');
            
            // Payment information
            $table->string('payment_mode'); // Monthly, Quarterly, Half Yearly, Yearly
            $table->decimal('contribution_amount', 10, 2);
            $table->string('medical_card_type'); // e-Medical Card, e-Medical Card & Physical Card
            $table->boolean('add_second_customer')->default(false);
            
            // Second customer information (if applicable)
            $table->string('second_customer_plan_type')->nullable();
            $table->string('second_customer_full_name')->nullable();
            $table->string('second_customer_nric', 14)->nullable();
            $table->string('second_customer_race')->nullable();
            $table->integer('second_customer_height_cm')->nullable();
            $table->integer('second_customer_weight_kg')->nullable();
            $table->string('second_customer_phone_number', 20)->nullable();
            $table->boolean('second_customer_medical_consultation_2_years')->nullable();
            $table->boolean('second_customer_serious_illness_history')->nullable();
            $table->boolean('second_customer_insurance_rejection_history')->nullable();
            $table->boolean('second_customer_serious_injury_history')->nullable();
            $table->string('second_customer_payment_mode')->nullable();
            $table->decimal('second_customer_contribution_amount', 10, 2)->nullable();
            $table->string('second_customer_medical_card_type')->nullable();
            $table->boolean('add_third_customer')->default(false);
            
            // Third customer information (if applicable)
            $table->string('third_customer_plan_type')->nullable();
            $table->string('third_customer_full_name')->nullable();
            $table->string('third_customer_nric', 14)->nullable();
            $table->string('third_customer_race')->nullable();
            $table->integer('third_customer_height_cm')->nullable();
            $table->integer('third_customer_weight_kg')->nullable();
            $table->string('third_customer_phone_number', 20)->nullable();
            $table->boolean('third_customer_medical_consultation_2_years')->nullable();
            $table->boolean('third_customer_serious_illness_history')->nullable();
            $table->boolean('third_customer_insurance_rejection_history')->nullable();
            $table->boolean('third_customer_serious_injury_history')->nullable();
            $table->string('third_customer_payment_mode')->nullable();
            $table->decimal('third_customer_contribution_amount', 10, 2)->nullable();
            $table->string('third_customer_medical_card_type')->nullable();
            $table->boolean('add_fourth_customer')->default(false);
            
            // Fourth customer information (if applicable)
            $table->string('fourth_customer_plan_type')->nullable();
            $table->string('fourth_customer_full_name')->nullable();
            $table->string('fourth_customer_nric', 14)->nullable();
            $table->string('fourth_customer_race')->nullable();
            $table->integer('fourth_customer_height_cm')->nullable();
            $table->integer('fourth_customer_weight_kg')->nullable();
            $table->string('fourth_customer_phone_number', 20)->nullable();
            $table->boolean('fourth_customer_medical_consultation_2_years')->nullable();
            $table->boolean('fourth_customer_serious_illness_history')->nullable();
            $table->boolean('fourth_customer_insurance_rejection_history')->nullable();
            $table->boolean('fourth_customer_serious_injury_history')->nullable();
            $table->string('fourth_customer_payment_mode')->nullable();
            $table->decimal('fourth_customer_contribution_amount', 10, 2)->nullable();
            $table->string('fourth_customer_medical_card_type')->nullable();
            $table->boolean('add_fifth_customer')->default(false);
            
            // Fifth customer information (if applicable)
            $table->string('fifth_customer_plan_type')->nullable();
            $table->string('fifth_customer_full_name')->nullable();
            $table->string('fifth_customer_nric', 14)->nullable();
            $table->string('fifth_customer_race')->nullable();
            $table->integer('fifth_customer_height_cm')->nullable();
            $table->integer('fifth_customer_weight_kg')->nullable();
            $table->string('fifth_customer_phone_number', 20)->nullable();
            $table->boolean('fifth_customer_medical_consultation_2_years')->nullable();
            $table->boolean('fifth_customer_serious_illness_history')->nullable();
            $table->boolean('fifth_customer_insurance_rejection_history')->nullable();
            $table->boolean('fifth_customer_serious_injury_history')->nullable();
            $table->string('fifth_customer_payment_mode')->nullable();
            $table->decimal('fifth_customer_contribution_amount', 10, 2)->nullable();
            $table->string('fifth_customer_medical_card_type')->nullable();
            $table->boolean('add_sixth_customer')->default(false);
            
            // Sixth customer information (if applicable)
            $table->string('sixth_customer_plan_type')->nullable();
            $table->string('sixth_customer_full_name')->nullable();
            $table->string('sixth_customer_nric', 14)->nullable();
            $table->string('sixth_customer_race')->nullable();
            $table->integer('sixth_customer_height_cm')->nullable();
            $table->integer('sixth_customer_weight_kg')->nullable();
            $table->string('sixth_customer_phone_number', 20)->nullable();
            $table->boolean('sixth_customer_medical_consultation_2_years')->nullable();
            $table->boolean('sixth_customer_serious_illness_history')->nullable();
            $table->boolean('sixth_customer_insurance_rejection_history')->nullable();
            $table->boolean('sixth_customer_serious_injury_history')->nullable();
            $table->string('sixth_customer_payment_mode')->nullable();
            $table->decimal('sixth_customer_contribution_amount', 10, 2)->nullable();
            $table->string('sixth_customer_medical_card_type')->nullable();
            $table->boolean('add_seventh_customer')->default(false);
            
            // Seventh customer information (if applicable)
            $table->string('seventh_customer_plan_type')->nullable();
            $table->string('seventh_customer_full_name')->nullable();
            $table->string('seventh_customer_nric', 14)->nullable();
            $table->string('seventh_customer_race')->nullable();
            $table->integer('seventh_customer_height_cm')->nullable();
            $table->integer('seventh_customer_weight_kg')->nullable();
            $table->string('seventh_customer_phone_number', 20)->nullable();
            $table->boolean('seventh_customer_medical_consultation_2_years')->nullable();
            $table->boolean('seventh_customer_serious_illness_history')->nullable();
            $table->boolean('seventh_customer_insurance_rejection_history')->nullable();
            $table->boolean('seventh_customer_serious_injury_history')->nullable();
            $table->string('seventh_customer_payment_mode')->nullable();
            $table->decimal('seventh_customer_contribution_amount', 10, 2)->nullable();
            $table->string('seventh_customer_medical_card_type')->nullable();
            $table->boolean('add_eighth_customer')->default(false);
            
            // Eighth customer information (if applicable)
            $table->string('eighth_customer_plan_type')->nullable();
            $table->string('eighth_customer_full_name')->nullable();
            $table->string('eighth_customer_nric', 14)->nullable();
            $table->string('eighth_customer_race')->nullable();
            $table->integer('eighth_customer_height_cm')->nullable();
            $table->integer('eighth_customer_weight_kg')->nullable();
            $table->string('eighth_customer_phone_number', 20)->nullable();
            $table->boolean('eighth_customer_medical_consultation_2_years')->nullable();
            $table->boolean('eighth_customer_serious_illness_history')->nullable();
            $table->boolean('eighth_customer_insurance_rejection_history')->nullable();
            $table->boolean('eighth_customer_serious_injury_history')->nullable();
            $table->string('eighth_customer_payment_mode')->nullable();
            $table->decimal('eighth_customer_contribution_amount', 10, 2)->nullable();
            $table->string('eighth_customer_medical_card_type')->nullable();
            $table->boolean('add_ninth_customer')->default(false);
            
            // Ninth customer information (if applicable)
            $table->string('ninth_customer_plan_type')->nullable();
            $table->string('ninth_customer_full_name')->nullable();
            $table->string('ninth_customer_nric', 14)->nullable();
            $table->string('ninth_customer_race')->nullable();
            $table->integer('ninth_customer_height_cm')->nullable();
            $table->integer('ninth_customer_weight_kg')->nullable();
            $table->string('ninth_customer_phone_number', 20)->nullable();
            $table->boolean('ninth_customer_medical_consultation_2_years')->nullable();
            $table->boolean('ninth_customer_serious_illness_history')->nullable();
            $table->boolean('ninth_customer_insurance_rejection_history')->nullable();
            $table->boolean('ninth_customer_serious_injury_history')->nullable();
            $table->string('ninth_customer_payment_mode')->nullable();
            $table->decimal('ninth_customer_contribution_amount', 10, 2)->nullable();
            $table->string('ninth_customer_medical_card_type')->nullable();
            $table->boolean('add_tenth_customer')->default(false);
            
            // Tenth customer information (if applicable)
            $table->string('tenth_customer_plan_type')->nullable();
            $table->string('tenth_customer_full_name')->nullable();
            $table->string('tenth_customer_nric', 14)->nullable();
            $table->string('tenth_customer_race')->nullable();
            $table->integer('tenth_customer_height_cm')->nullable();
            $table->integer('tenth_customer_weight_kg')->nullable();
            $table->string('tenth_customer_phone_number', 20)->nullable();
            $table->boolean('tenth_customer_medical_consultation_2_years')->nullable();
            $table->boolean('tenth_customer_serious_illness_history')->nullable();
            $table->boolean('tenth_customer_insurance_rejection_history')->nullable();
            $table->boolean('tenth_customer_serious_injury_history')->nullable();
            $table->string('tenth_customer_payment_mode')->nullable();
            $table->decimal('tenth_customer_contribution_amount', 10, 2)->nullable();
            $table->string('tenth_customer_medical_card_type')->nullable();
            
            // Status and processing
            $table->enum('status', ['pending', 'approved', 'rejected', 'payment_pending', 'active', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('payment_completed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_insurance_registrations');
    }
};
