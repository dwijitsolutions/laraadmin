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

class CreateLAModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('la_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('label', 100);
            $table->string('name_db', 50);
            $table->string('view_col', 50);
            $table->string('model', 50);
            $table->string('controller', 100);
            $table->string('fa_icon', 30)->default('fa-cube');
            $table->boolean('is_gen');
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
        Schema::drop('la_modules');
    }
}
