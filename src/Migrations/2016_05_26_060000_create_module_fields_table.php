<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuleFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->string('colname', 30);
            $table->string('label', 100);
            $table->integer('module')->unsigned();
            $table->integer('field_type')->unsigned();
            $table->boolean('readonly')->default(false);
            $table->string('defaultvalue');
            $table->integer('minlength')->unsigned();
            $table->integer('maxlength')->unsigned();
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
        Schema::drop('module_fields');
    }
}
