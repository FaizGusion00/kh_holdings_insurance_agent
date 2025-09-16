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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable(); // Hospital code for easy reference
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('phone_number')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('services_offered')->nullable(); // JSON array of services
            $table->boolean('is_panel')->default(true); // Is this a panel hospital
            $table->boolean('is_active')->default(true);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('operating_hours')->nullable(); // JSON format
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('is_panel');
            $table->index('is_active');
            $table->index('state');
            $table->index('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
