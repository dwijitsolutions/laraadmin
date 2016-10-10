<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('label', 100);
            $table->string('name_db', 50);
            $table->string('view_col', 50);
            $table->string('model', 50);
            $table->string('controller', 100);
            $table->string('fa_icon', 30)->default("fa-cube");
            $table->boolean('is_gen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('modules');
    }
}
