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
            $table->boolean('unique')->default(false);
            $table->string('defaultvalue');
            $table->integer('minlength')->unsigned();
            $table->integer('maxlength')->unsigned();
            $table->boolean('required')->default(false);
            $table->text('popup_vals');
            $table->integer('sort')->unsigned()->default(0);
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
