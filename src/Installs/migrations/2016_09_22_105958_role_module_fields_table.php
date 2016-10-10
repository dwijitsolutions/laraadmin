<?php

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
			$table->integer('role_id')->unsigned();
			$table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
			$table->integer('field_id')->unsigned();
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
