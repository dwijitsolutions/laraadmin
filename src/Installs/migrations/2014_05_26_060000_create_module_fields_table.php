<?php
/**
 * Migration generated using LaraAdmin
 * Help: http://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: http://dwijitsolutions.com
 */

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
            $table->unsignedInteger('module')->nullable();
            $table->foreign('module')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('field_type')->nullable();
            $table->foreign('field_type')->references('id')->on('module_field_types')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('unique')->default(false);
            $table->string('defaultvalue')->nullable();
            $table->integer('minlength')->unsigned()->default(0);
            $table->integer('maxlength')->unsigned()->default(0);
            $table->boolean('required')->default(false);
            $table->text('popup_vals');
            $table->integer('sort')->unsigned()->default(0);
			$table->boolean('listing_col')->default(true);
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
