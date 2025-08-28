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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('nric', 12)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('occupation')->nullable();
            $table->string('race', 50)->nullable();
            $table->string('relationship_with_agent', 100)->nullable();
            $table->enum('status', ['active', 'pending', 'suspended', 'terminated'])->default('pending');
            $table->date('registration_date');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 15)->nullable();
            $table->string('emergency_contact_relationship', 100)->nullable();
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->string('referrer_code')->nullable();
            $table->foreignId('referrer_id')->nullable()->constrained('users')->after('referrer_code');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['registration_date']);
            $table->index(['phone']);
            $table->index(['referrer_code']);
            $table->index(['referrer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
