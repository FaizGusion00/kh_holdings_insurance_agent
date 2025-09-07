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
        Schema::create('gateway_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('medical_insurance_registrations')->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->string('gateway'); // e.g., curlec
            $table->string('payment_id')->index();
            $table->string('order_id')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MYR');
            $table->string('status');
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->json('gateway_response')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['registration_id', 'status']);
            $table->index(['agent_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_payments');
    }
};


