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

class RoleLAModuleFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_la_module_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->unsignedBigInteger('field_id')->unsigned();
            $table->foreign('field_id')->references('id')->on('la_module_fields');
            $table->string('access', 10);
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
        if (Schema::hasTable('role_la_module_fields')) {
            Schema::drop('role_la_module_fields');
        }
    }
}
