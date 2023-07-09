<?php
/***
 * Migration generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

use App\Models\LAModuleFieldType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLAModuleFieldTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('la_module_field_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->timestamps();
        });
        // Note: Do not edit below lines
        LAModuleFieldType::create(['name' => 'Address']);
        LAModuleFieldType::create(['name' => 'Checkbox']);
        LAModuleFieldType::create(['name' => 'Currency']);
        LAModuleFieldType::create(['name' => 'Date']);
        LAModuleFieldType::create(['name' => 'Datetime']);
        LAModuleFieldType::create(['name' => 'Decimal']);
        LAModuleFieldType::create(['name' => 'Dropdown']);
        LAModuleFieldType::create(['name' => 'Email']);
        LAModuleFieldType::create(['name' => 'File']);
        LAModuleFieldType::create(['name' => 'Float']);
        LAModuleFieldType::create(['name' => 'HTML']);
        LAModuleFieldType::create(['name' => 'Image']);
        LAModuleFieldType::create(['name' => 'Integer']);
        LAModuleFieldType::create(['name' => 'Mobile']);
        LAModuleFieldType::create(['name' => 'Multiselect']);
        LAModuleFieldType::create(['name' => 'Name']);
        LAModuleFieldType::create(['name' => 'Password']);
        LAModuleFieldType::create(['name' => 'Radio']);
        LAModuleFieldType::create(['name' => 'String']);
        LAModuleFieldType::create(['name' => 'Taginput']);
        LAModuleFieldType::create(['name' => 'Textarea']);
        LAModuleFieldType::create(['name' => 'TextField']);
        LAModuleFieldType::create(['name' => 'URL']);
        LAModuleFieldType::create(['name' => 'Files']);
        LAModuleFieldType::create(['name' => 'Location']);
        LAModuleFieldType::create(['name' => 'Color']);
        LAModuleFieldType::create(['name' => 'Time']);
        LAModuleFieldType::create(['name' => 'JSON']);
        LAModuleFieldType::create(['name' => 'List']);
        LAModuleFieldType::create(['name' => 'Duration']);
        LAModuleFieldType::create(['name' => 'Checklist']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('la_module_field_types');
    }
}
