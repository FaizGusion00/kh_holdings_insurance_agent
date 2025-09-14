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
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('wallet_transaction_id')->nullable()->after('payment_date')->constrained('wallet_transactions')->onDelete('set null');
            $table->index('wallet_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['wallet_transaction_id']);
            $table->dropIndex(['wallet_transaction_id']);
            $table->dropColumn('wallet_transaction_id');
        });
    }
};
