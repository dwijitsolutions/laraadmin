<?php
/***
 * Migration generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLAModuleFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('la_module_fields', function (Blueprint $table) {
            $table->id();
            $table->string('colname', 30);
            $table->string('label', 100);
            $table->unsignedBigInteger('module')->unsigned();
            $table->foreign('module')->references('id')->on('la_modules');
            $table->unsignedBigInteger('field_type')->unsigned();
            $table->foreign('field_type')->references('id')->on('la_module_field_types');
            $table->boolean('unique')->default(false);
            $table->string('defaultvalue')->default('')->nullable();
            $table->integer('minlength')->default(0);
            $table->integer('maxlength')->unsigned()->default(0);
            $table->boolean('required')->default(false);
            $table->text('popup_vals');
            $table->integer('sort')->unsigned()->default(0);
            $table->boolean('listing_col')->default(true);
            $table->string('comment')->default('')->nullable();
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
        Schema::drop('la_module_fields');
    }
}
