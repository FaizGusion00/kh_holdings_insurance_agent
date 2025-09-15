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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // commission, policy, purchase, expiry, system, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the notification
            $table->string('action_url')->nullable(); // URL to navigate when clicked
            $table->string('action_text')->nullable(); // Text for the action button
            $table->boolean('is_read')->default(false);
            $table->boolean('is_important')->default(false); // For urgent notifications
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Auto-delete after this date
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};