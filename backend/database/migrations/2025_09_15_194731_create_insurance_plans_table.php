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
        Schema::create('insurance_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name')->unique();
            $table->string('plan_code')->unique(); // MediPlan_Coop, Senior_Care_Gold_270, etc.
            $table->text('description');
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->decimal('quarterly_price', 10, 2)->nullable();
            $table->decimal('semi_annually_price', 10, 2)->nullable();
            $table->decimal('annually_price', 10, 2)->nullable();
            $table->decimal('commitment_fee', 10, 2)->default(0); // For Senior Care plans
            $table->decimal('room_board_limit', 10, 2)->nullable(); // Daily room & board limit
            $table->decimal('annual_limit', 15, 2); // Overall annual limit
            $table->decimal('government_cash_allowance', 10, 2)->default(0); // Daily cash allowance in govt hospital
            $table->decimal('death_benefit', 15, 2)->default(0); // Bereavement/Death benefit
            $table->integer('min_age'); // Minimum entry age
            $table->integer('max_age'); // Maximum entry age
            $table->integer('renewal_age')->nullable(); // Maximum renewal age
            $table->text('benefits')->nullable(); // JSON or text field for plan benefits
            $table->text('terms_conditions')->nullable(); // Terms and conditions
            $table->integer('waiting_period_general')->default(90); // Days for general illnesses
            $table->integer('waiting_period_specific')->default(180); // Days for specific illnesses
            $table->string('administrator')->nullable(); // Third party administrator (e.g., eMAS)
            $table->integer('panel_hospitals')->default(0); // Number of panel hospitals
            $table->integer('panel_clinics')->default(0); // Number of panel clinics
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('plan_code');
            $table->index('is_active');
            $table->index('min_age');
            $table->index('max_age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_plans');
    }
};
