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
        Schema::table('users', function (Blueprint $table) {
            // Plan and Policy Information (from clients table)
            $table->string('plan_name')->nullable()->after('mlm_activation_date');
            $table->string('payment_mode')->nullable()->after('plan_name'); // monthly, yearly, etc.
            $table->string('medical_card_type')->nullable()->after('payment_mode');
            $table->string('customer_type')->nullable()->after('medical_card_type'); // primary, second, third
            $table->foreignId('registration_id')->nullable()->constrained('medical_insurance_registrations')->nullOnDelete()->after('customer_type');
            
            // Demographics (from members table)
            $table->string('race', 50)->nullable()->after('nric');
            $table->date('date_of_birth')->nullable()->after('race');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->string('occupation')->nullable()->after('gender');
            $table->integer('height_cm')->nullable()->after('occupation');
            $table->integer('weight_kg')->nullable()->after('height_cm');
            
            // Emergency Contact (from members/registrations table)
            $table->string('emergency_contact_name')->nullable()->after('weight_kg');
            $table->string('emergency_contact_phone', 15)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship', 100)->nullable()->after('emergency_contact_phone');
            
            // Medical History (from registrations table)
            $table->boolean('medical_consultation_2_years')->default(false)->after('emergency_contact_relationship');
            $table->boolean('serious_illness_history')->default(false)->after('medical_consultation_2_years');
            $table->boolean('insurance_rejection_history')->default(false)->after('serious_illness_history');
            $table->boolean('serious_injury_history')->default(false)->after('insurance_rejection_history');
            
            // Registration and Financial Info
            $table->date('registration_date')->nullable()->after('serious_injury_history');
            $table->string('relationship_with_agent', 100)->nullable()->after('registration_date');
            $table->decimal('balance', 10, 2)->default(0.00)->after('relationship_with_agent');
            $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('balance');
            
            // Indexes for better performance on new columns
            $table->index(['plan_name']);
            $table->index(['customer_type']);
            $table->index(['registration_id']);
            $table->index(['registration_date']);
            $table->index(['race']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['plan_name']);
            $table->dropIndex(['customer_type']);
            $table->dropIndex(['registration_id']);
            $table->dropIndex(['registration_date']);
            $table->dropIndex(['race']);
            
            // Drop foreign key constraint
            $table->dropForeign(['registration_id']);
            
            // Drop all the added columns
            $table->dropColumn([
                'plan_name',
                'payment_mode',
                'medical_card_type',
                'customer_type',
                'registration_id',
                'race',
                'date_of_birth',
                'gender',
                'occupation',
                'height_cm',
                'weight_kg',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'medical_consultation_2_years',
                'serious_illness_history',
                'insurance_rejection_history',
                'serious_injury_history',
                'registration_date',
                'relationship_with_agent',
                'balance',
                'wallet_balance',
            ]);
        });
    }
};
