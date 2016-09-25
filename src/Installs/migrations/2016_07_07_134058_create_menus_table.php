<?php
/**
 * Migration genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\Menu;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('la_menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('url', 256);
            $table->string('icon', 50)->default("fa-cube");
            $table->string('type', 20)->default("module");
            $table->integer('parent')->unsigned()->default(0);
            $table->integer('hierarchy')->unsigned()->default(0);
            $table->timestamps();
        });

        // Generating Module Menus
        $modules = Module::all();
        foreach ($modules as $module) {
            Menu::create([
                "name" => $module->name,
                "url" => $module->name_db,
                "icon" => $module->fa_icon,
                "type" => 'module',
                "parent" => 0
            ]);
        }
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
