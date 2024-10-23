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
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('token');
            $table->string('reset_password_code')->nullable();
            $table->timestamp('reset_password_code_expires_at')->nullable(); // Added expiry for password reset code
            $table->timestamp('reset_password_token_expires_at')->nullable(); // Added expiry for password reset token
            $table->timestamps();
        });
        
        // Migrate data to password_resets table
        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => $user->password_reset_token,
                    'reset_password_code' => $user-reset_password_code,
                    'reset_password_expires_at' => $user->reset_password_expires_at,
                    'reset_password_code_expires_at' => $user->reset_password_code_expires_at,
                    'reset_password_token_expires_at' => $user-reset_password_token_expires_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $usereset_password
                ]);
            }
        });
    
            // Remove columns from users table if they exist
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'reset_password_code')) {
                    $table->dropColumn('reset_password_code');
                }
                if (Schema::hasColumn('users', 'reset_password_expires_at')) {
                    $table->dropColumn('reset_password_expires_at');
                }
                if (Schema::hasColumn('users', 'reset_password_token')) {
                    $table->dropColumn('reset_password_token');
                }
                if (Schema::hasColumn('users', 'reset_password_token_expires_at')) {
                    $table->dropColumn('reset_password_token_expires_at');
                }
            });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_resets');
        Schema::table('users', function (Blueprint $table) {
            $table->string('reset_password_code')->nullable();
            $table->timestamp('reset_password_expires_at')->nullable();
            $table->string('reset_password_token')->nullable();
            $table->timestamp('reset_password_token_expires_at')->nullable();
        }); 
    }
};
