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

class CreateLAMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('la_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('url', 256);
            $table->string('icon', 50)->default('fa-cube');
            $table->string('type', 20)->default('module');
            $table->integer('parent')->unsigned()->default(0);
            $table->integer('hierarchy')->unsigned()->default(0);
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
        if (Schema::hasTable('la_menus')) {
            Schema::drop('la_menus');
        }
    }
}
