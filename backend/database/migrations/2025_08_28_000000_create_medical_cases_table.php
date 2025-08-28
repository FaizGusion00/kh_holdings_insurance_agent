<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->enum('case_type', ['hospital', 'clinic'])->index();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->timestamp('approved_at')->nullable()->index();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'case_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_cases');
    }
};


