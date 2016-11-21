<?php
/**
 * Migration generated using LaraAdmin
 * Help: http://laraadmin.com
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Dwij\Laraadmin\Models\Module;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Module::generate("Users", 'users', 'name', 'fa-group', [
            [
                "colname" => "name",
                "label" => "Name",
                "field_type" => "Name",
                "unique" => false,
                "defaultvalue" => "",
                "minlength" => 5,
                "maxlength" => 250,
                "required" => true,
                "browse" => true
            ], [
                "colname" => "context_id",
                "label" => "Context",
                "field_type" => "Integer",
                "unique" => false,
                "defaultvalue" => "0",
                "minlength" => 0,
                "maxlength" => 0,
                "required" => false,
                "browse" => true
            ], [
                "colname" => "email",
                "label" => "Email",
                "field_type" => "Email",
                "unique" => true,
                "defaultvalue" => "",
                "minlength" => 0,
                "maxlength" => 250,
                "required" => false,
                "browse" => true
            ], [
                "colname" => "password",
                "label" => "Password",
                "field_type" => "Password",
                "unique" => false,
                "defaultvalue" => "",
                "minlength" => 6,
                "maxlength" => 250,
                "required" => true,
                "browse" => true
            ], [
                "colname" => "type",
                "label" => "User Type",
                "field_type" => "Dropdown",
                "unique" => false,
                "defaultvalue" => "Employee",
                "minlength" => 0,
                "maxlength" => 0,
                "required" => false,
                "browse" => true,
                "popup_vals" => ["Employee","Client"],
            ]
        ]);
        
        /*
        Module::generate("Module_Name", "Table_Name", "view_column_name" "Fields_Array");

        Field Format:
        [
            "colname" => "name",
            "label" => "Name",
            "field_type" => "Name",
            "unique" => false,
            "defaultvalue" => "John Doe",
            "minlength" => 5,
            "maxlength" => 100,
            "required" => true,
            "browse" => true,
            "popup_vals" => ["Employee", "Client"]
        ]
        # Format Details: Check http://laraadmin.com/docs/migrations_cruds#schema-ui-types
        colname: Database column name. lowercase, words concatenated by underscore (_)
        label: Label of Column e.g. Name, Cost, Is Public
        field_type: It defines type of Column in more General way.
        unique: Whether the column has unique values. Value in true / false
        defaultvalue: Default value for column.
        minlength: Minimum Length of value in integer.
        maxlength: Maximum Length of value in integer.
        required: Is this mandatory field in Add / Edit forms. Value in true / false
        browse: Is allowed to show in index page datatable.
        popup_vals: These are values for MultiSelect, TagInput and Radio Columns. Either connecting @tables or to list []
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::drop('users');
        }
    }
}
