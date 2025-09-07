<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('registration_id')->constrained('medical_insurance_registrations')->onDelete('cascade');
            $table->string('customer_type'); // primary, second, ...
            $table->string('full_name');
            $table->string('nric');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->string('plan_name');
            $table->string('payment_mode'); // monthly/yearly
            $table->string('medical_card_type');
            $table->string('status')->default('active');
            $table->foreignId('policy_id')->nullable()->constrained('medical_insurance_policies')->nullOnDelete();
            $table->timestamps();

            $table->index(['agent_id', 'status']);
            $table->index(['registration_id']);
            $table->index(['nric']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};


