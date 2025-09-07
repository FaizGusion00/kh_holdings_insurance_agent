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
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('medical_insurance_registrations')->onDelete('cascade');
            $table->string('customer_type'); // primary, second, third, fourth, etc.
            $table->string('contact_name');
            $table->string('contact_phone', 20);
            $table->string('contact_relationship');
            $table->timestamps();
            
            $table->index(['registration_id', 'customer_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};