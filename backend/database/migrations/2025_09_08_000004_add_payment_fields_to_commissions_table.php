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
            $table->text('admin_notes')->nullable()->after('payment_date');
            $table->string('payment_proof')->nullable()->after('admin_notes');
            $table->string('payment_method')->nullable()->after('payment_proof');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->foreignId('paid_by_admin_id')->nullable()->after('payment_reference')->constrained('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['paid_by_admin_id']);
            $table->dropColumn([
                'admin_notes',
                'payment_proof',
                'payment_method',
                'payment_reference',
                'paid_by_admin_id'
            ]);
        });
    }
};
