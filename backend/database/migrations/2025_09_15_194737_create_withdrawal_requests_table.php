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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('request_id')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_owner');
            $table->enum('status', ['pending', 'processing', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable(); // Will be foreign key to admins table
            $table->string('transaction_reference')->nullable(); // Bank transfer reference
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('request_id');
            $table->index('status');
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
