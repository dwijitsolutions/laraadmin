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

class CreatePermissionRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for associating permissions to roles (Many-to-Many)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id')->unsigned();
            $table->unsignedBigInteger('role_id')->unsigned();

            $table->foreign('permission_id')->references('id')->on('permissions');
            $table->foreign('role_id')->references('id')->on('roles');

            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('permission_role')) {
            Schema::drop('permission_role');
        }
    }
}
