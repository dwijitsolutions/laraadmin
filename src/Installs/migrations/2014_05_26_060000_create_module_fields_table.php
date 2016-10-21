<?php

use Illuminate\Support\Facades\Schema;
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
            $table->foreign('module')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('field_type')->unsigned();
            $table->foreign('field_type')->references('id')->on('module_field_types')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('unique')->default(false);
            $table->string('defaultvalue');
            $table->integer('minlength')->unsigned()->default(0);
            $table->integer('maxlength')->unsigned()->default(0);
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
