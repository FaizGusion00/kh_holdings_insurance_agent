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
            // Insurance plan tracking fields
            if (!Schema::hasColumn('users', 'current_insurance_plan_id')) {
                $table->unsignedBigInteger('current_insurance_plan_id')->nullable()->after('customer_type');
                $table->foreign('current_insurance_plan_id')->references('id')->on('insurance_plans')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('users', 'policy_start_date')) {
                $table->date('policy_start_date')->nullable()->after('current_insurance_plan_id');
            }
            
            if (!Schema::hasColumn('users', 'policy_end_date')) {
                $table->date('policy_end_date')->nullable()->after('policy_start_date');
            }
            
            if (!Schema::hasColumn('users', 'next_payment_due')) {
                $table->date('next_payment_due')->nullable()->after('policy_end_date');
            }
            
            if (!Schema::hasColumn('users', 'policy_status')) {
                $table->enum('policy_status', ['active', 'inactive', 'pending', 'expired', 'cancelled'])->nullable()->after('next_payment_due');
            }
            
            if (!Schema::hasColumn('users', 'premium_amount')) {
                $table->decimal('premium_amount', 10, 2)->nullable()->after('policy_status');
            }
            
            if (!Schema::hasColumn('users', 'current_payment_mode')) {
                $table->enum('current_payment_mode', ['monthly', 'quarterly', 'semi_annually', 'annually'])->nullable()->after('premium_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_insurance_plan_id']);
            $table->dropColumn([
                'current_insurance_plan_id',
                'policy_start_date', 
                'policy_end_date',
                'next_payment_due',
                'policy_status',
                'premium_amount',
                'current_payment_mode'
            ]);
        });
    }
};