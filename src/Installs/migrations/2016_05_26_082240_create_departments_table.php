<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use app\Department;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('tags');
            $table->string('color');
            $table->integer('head')->unsigned();
            $table->timestamps();
        });
        
        //if (Schema::hasTable('employees')) {
            // Schema::table('employees', function ($table) {
            //     $table->foreign('dept')->references('id')->on('departments');
            // });
        // }
        // if (Schema::hasTable('roles')) {
            // Schema::table('roles', function ($table) {
            //     $table->foreign('dept')->references('id')->on('departments');
            // });
        // }
        
        // $table->integer('head')->unsigned();
        // $table->foreign('head')->references('id')->on('employees');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('departments');
    }
}
