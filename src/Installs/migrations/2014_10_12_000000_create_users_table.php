<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('context_id')->unsigned();
            $table->string('name');
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('type', 10)->default("employee");
            $table->rememberToken();
            $table->timestamps();
        });
        User::create([
            'name' => "Super Admin",
            'email' => "laraadmin@gmail.com",
            'password' => bcrypt("12345678"),
            'context_id' => "1",
            'type' => "employee",
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
