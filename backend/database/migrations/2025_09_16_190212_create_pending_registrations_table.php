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
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id'); // The agent registering the clients
            $table->string('registration_batch_id')->unique(); // Unique batch identifier
            $table->json('clients_data'); // Store all client registration data as JSON
            $table->decimal('total_amount', 10, 2); // Total payment amount for all clients
            $table->enum('status', ['pending_payment', 'payment_completed', 'cancelled', 'expired'])->default('pending_payment');
            $table->unsignedBigInteger('payment_transaction_id')->nullable(); // Link to payment transaction
            $table->timestamp('expires_at'); // Registration expires after some time
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payment_transaction_id')->references('id')->on('payment_transactions')->onDelete('set null');
            
            // Indexes
            $table->index('agent_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};