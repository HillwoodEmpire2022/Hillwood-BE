<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role')->default(2);
            $table->string('fname');
            $table->string('lname');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('gender')->nullable();
            $table->string('profile')->default('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTpCKq1XnPYYDaUIlwlsvmLPZ-9-rdK28RToA&usqp=CAU');
            $table->string('address')->nullable();
            $table->string('dob')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('role')->references('id')->on('roles')->onDelete('cascade');
        });

        User::create([
            "role" => 1,
            "fname" => "Test",
            "lname" => "administrator",
            "phone" => "0788888888",
            "email" => "admin@gmail.com",
            'password' => Hash::make('admin@gmail.com'),
            "gender" => "male",
        ]);

        User::create([
            "role" => 2,
            "fname" => "Test",
            "lname" => "user",
            "phone" => "0787777777",
            "email" => "user@gmail.com",
            'password' => Hash::make('user@gmail.com'),
            "gender" => "female",
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
