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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null'); // User who triggered the commission
            $table->string('reference_id')->nullable(); // Payment transaction ID or other reference
            $table->enum('type', ['commission_earned', 'withdrawal', 'bonus', 'penalty', 'transfer_in', 'transfer_out']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('description');
            $table->text('metadata')->nullable(); // JSON data for additional info
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('related_user_id');
            $table->index('type');
            $table->index('reference_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
