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
        // Add reset password code and expiry date columns to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('reset_password_code')->nullable();
            $table->timestamp('reset_password_expires_at')->nullable();
            $table->string('reset_password_token')->nullable();
            $table->timestamp('reset_password_token_expires_at')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop reset password code and expiry date columns from the users table if they exist 
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('reset_password_code');
            $table->dropColumn('reset_password_expires_at');
        });
    }
};
