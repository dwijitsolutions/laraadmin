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

class RoleModuleFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_module_fields', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('role_id')->nullable();
			$table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
			$table->unsignedInteger('field_id')->nullable();
			$table->foreign('field_id')->references('id')->on('module_fields')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('access', ['invisible', 'readonly', 'write']);
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
        if (Schema::hasTable('role_module_fields')) {
            Schema::drop('role_module_fields');
        }
    }
}
