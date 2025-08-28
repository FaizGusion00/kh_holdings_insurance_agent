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
            // Add MLM-specific fields
            $table->string('agent_number', 6)->unique()->after('id');
            $table->string('agent_code', 10)->unique()->after('agent_number');
            $table->string('referrer_code', 10)->nullable()->after('agent_code');
            $table->string('phone_number', 15)->nullable()->after('email');
            $table->string('nric', 12)->unique()->nullable()->after('phone_number');
            $table->text('address')->nullable()->after('nric');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('postal_code', 10)->nullable()->after('state');
            $table->string('bank_name', 100)->nullable()->after('postal_code');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_account_owner', 255)->nullable()->after('bank_account_number');
            $table->tinyInteger('mlm_level')->default(1)->after('bank_account_owner');
            $table->decimal('total_commission_earned', 15, 2)->default(0)->after('mlm_level');
            $table->decimal('monthly_commission_target', 15, 2)->default(0)->after('total_commission_earned');
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])->default('pending')->after('monthly_commission_target');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('mlm_activation_date')->nullable()->after('phone_verified_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'agent_number', 'agent_code', 'referrer_code', 'phone_number', 'nric',
                'address', 'city', 'state', 'postal_code', 'bank_name', 'bank_account_number',
                'bank_account_owner', 'mlm_level', 'total_commission_earned', 'monthly_commission_target',
                'status', 'phone_verified_at', 'mlm_activation_date'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
