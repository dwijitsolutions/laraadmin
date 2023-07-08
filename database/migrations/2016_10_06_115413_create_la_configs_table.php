<?php
/**
 * Migration generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('la_configs', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100);
            $table->string('key', 50)->unique();
            $table->string('section', 100)->default("")->nullable();
            $table->string('value', 5000)->nullable()->default(null);
            $table->unsignedBigInteger('field_type')->unsigned()->nullable()->default(null);
            $table->foreign('field_type')->references('id')->on('la_module_field_types');
            $table->integer('minlength')->unsigned()->nullable()->default(null);
            $table->integer('maxlength')->unsigned()->nullable()->default(null);
            $table->boolean('required')->default(false);
            $table->string('popup_vals', 5000)->default("")->nullable();
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
        Schema::drop('la_configs');
    }
}
