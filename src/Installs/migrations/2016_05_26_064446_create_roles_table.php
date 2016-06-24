<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Role;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('name_short');
            $table->integer('parent')->unsigned();
            $table->integer('dept')->unsigned();
            $table->timestamps();
        });
        
        Role::create([
            'name' => "Super Admin",
            'name_short' => "SUPER",
            'prent' => 0,
            'dept' => 0
        ]);
        
        // if (Schema::hasTable('employees')) {
            // Schema::table('employees', function ($table) {
            //     $table->foreign('role')->references('id')->on('roles');
            // });
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('roles');
    }
}
