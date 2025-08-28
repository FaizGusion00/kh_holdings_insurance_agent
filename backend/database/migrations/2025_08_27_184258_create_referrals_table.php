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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('agent_code', 10)->unique();
            $table->string('referrer_code', 10)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('referral_level')->default(1);
            $table->json('upline_chain')->nullable();
            $table->integer('downline_count')->default(0);
            $table->integer('total_downline_count')->default(0);
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])->default('pending');
            $table->timestamp('activation_date')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['agent_code', 'status']);
            $table->index(['referrer_code', 'status']);
            $table->index('referral_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
