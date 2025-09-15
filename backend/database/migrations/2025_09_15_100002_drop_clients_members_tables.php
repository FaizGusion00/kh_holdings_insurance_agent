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
        // Drop foreign key constraints that actually exist
        if (Schema::hasTable('gateway_payments')) {
            Schema::table('gateway_payments', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });
        }

        if (Schema::hasTable('member_policies')) {
            Schema::table('member_policies', function (Blueprint $table) {
                $table->dropForeign(['member_id']);
            });
        }

        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropForeign(['member_id']);
                $table->dropColumn('member_id');
            });
        }

        // Drop the clients table
        Schema::dropIfExists('clients');

        // Drop the members table  
        Schema::dropIfExists('members');

        // Also drop product_commission_rules as requested earlier
        Schema::dropIfExists('product_commission_rules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed as it drops data
        // The down method is intentionally left empty
        // To reverse, you would need to recreate the tables and redistribute the data
    }
};
