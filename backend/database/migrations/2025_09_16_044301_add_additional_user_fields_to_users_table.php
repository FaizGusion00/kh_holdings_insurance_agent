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
        Schema::table('users', function (Blueprint $table) {
            // Bank information
            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('users', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('users', 'bank_account_owner')) {
                $table->string('bank_account_owner')->nullable()->after('bank_account_number');
            }
            
            // Emergency contact fields
            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('bank_account_owner');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            }
            
            // Relationship with agent
            if (!Schema::hasColumn('users', 'relationship_with_agent')) {
                $table->string('relationship_with_agent')->nullable()->after('emergency_contact_relationship');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_number', 
                'bank_account_owner',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'relationship_with_agent'
            ]);
        });
    }
};