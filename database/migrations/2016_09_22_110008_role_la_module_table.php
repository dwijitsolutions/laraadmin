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

class RoleLAModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_la_module', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->unsignedBigInteger('module_id')->unsigned();
            $table->foreign('module_id')->references('id')->on('la_modules');
            $table->boolean('acc_view');
            $table->boolean('acc_create');
            $table->boolean('acc_edit');
            $table->boolean('acc_delete');
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
        if (Schema::hasTable('role_la_module')) {
            Schema::drop('role_la_module');
        }
    }
}
