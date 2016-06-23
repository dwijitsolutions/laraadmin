<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Dwij\Laraadmin\Models\ModuleFieldTypes;

class CreateModuleFieldTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_field_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);
            $table->timestamps();
        });
        // Note: Do not edit below lines
        ModuleFieldTypes::create(["name" => "Address"]);
        ModuleFieldTypes::create(["name" => "Checkbox"]);
        ModuleFieldTypes::create(["name" => "Currency"]);
        ModuleFieldTypes::create(["name" => "Date"]);
        ModuleFieldTypes::create(["name" => "Datetime"]);
        ModuleFieldTypes::create(["name" => "Decimal"]);
        ModuleFieldTypes::create(["name" => "Dropdown"]);
        ModuleFieldTypes::create(["name" => "Email"]);
        ModuleFieldTypes::create(["name" => "File"]);
        ModuleFieldTypes::create(["name" => "Float"]);
        ModuleFieldTypes::create(["name" => "HTML"]);
        ModuleFieldTypes::create(["name" => "Image"]);
        ModuleFieldTypes::create(["name" => "Integer"]);
        ModuleFieldTypes::create(["name" => "Mobile"]);
        ModuleFieldTypes::create(["name" => "Multiselect"]);
        ModuleFieldTypes::create(["name" => "Name"]);
        ModuleFieldTypes::create(["name" => "Password"]);
        ModuleFieldTypes::create(["name" => "Radio"]);
        ModuleFieldTypes::create(["name" => "String"]);
        ModuleFieldTypes::create(["name" => "Taginput"]);
        ModuleFieldTypes::create(["name" => "Textarea"]);
        ModuleFieldTypes::create(["name" => "TextField"]);
        ModuleFieldTypes::create(["name" => "URL"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('module_field_types');
    }
}
