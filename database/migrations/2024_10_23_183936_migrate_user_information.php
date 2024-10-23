<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateUserInformation extends Migration
{
    public function up()
    {
        // Create new tables
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('gender')->nullable();
            $table->string('profile_image')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('verification_code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Migrate data to new tables
        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                DB::table('user_profiles')->insert([
                    'user_id' => $user->id,
                    'address' => $user->address,
                    'phone_number' => $user->phone_number,
                    'gender' => $user->gender,
                    'profile_image' => $user->profile_image,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);

                DB::table('user_verifications')->insert([
                    'user_id' => $user->id,
                    'verification_code' => $user->verification_code,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        });

        // Remove columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address', 'phone_number', 'gender', 'profile_image', 'verification_code', 'email_verified_at']);
        });
    }

    public function down()
    {
        // Rollback changes
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('gender')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
        });

        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('user_verifications');
    }
}