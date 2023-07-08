<?php
/**
 * Migration generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\LAModule;

class CreateLALogsTable extends Migration
{
    /**
     * Migration generate Module Table Schema by LaraAdmin
     *
     * @return void
     */
    public function up()
    {
        LAModule::generate("LA_logs", 'la_logs', 'title', 'fa-eye', [
            [
                "colname" => "title",
                "label" => "Title",
                "field_type" => "Name",
                "unique" => false,
                "defaultvalue" => "",
                "minlength" => 0,
                "maxlength" => 256,
                "required" => true,
                "listing_col" => true
            ], [
                "colname" => "type",
                "label" => "Type",
                "field_type" => "String",
                "unique" => false,
                "defaultvalue" => "",
                "minlength" => 1,
                "maxlength" => 256,
                "required" => true,
                "listing_col" => true
            ], [
                "colname" => "module_id",
                "label" => "Module",
                "field_type" => "Dropdown",
                "unique" => false,
                "defaultvalue" => NULL,
                "required" => false,
                "listing_col" => true,
                "popup_vals" => "@la_modules",
            ], [
                "colname" => "context_id",
                "label" => "Context",
                "field_type" => "Integer",
                "unique" => false,
                "defaultvalue" => NULL,
                "minlength" => 0,
                "maxlength" => 11,
                "required" => true,
                "listing_col" => true
            ], [
                "colname" => "context2_id",
                "label" => "Context2",
                "field_type" => "Integer",
                "unique" => false,
                "defaultvalue" => 0,
                "minlength" => 0,
                "maxlength" => 11,
                "required" => false,
                "listing_col" => true
            ], [
                "colname" => "content",
                "label" => "Content",
                "field_type" => "Textarea",
                "unique" => false,
                "defaultvalue" => "{}",
                "minlength" => 0,
                "maxlength" => 0,
                "required" => false,
                "listing_col" => false
            ], [
                "colname" => "user_id",
                "label" => "User",
                "field_type" => "Dropdown",
                "unique" => false,
                "defaultvalue" => NULL,
                "required" => true,
                "listing_col" => true,
                "popup_vals" => "@users"
            ]
        ]);
        
        /*
        LAModule::generate("Module_Name", "Table_Name", "view_column_name" "Fields_Array");

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
            "listing_col" => true,
            "popup_vals" => ["Employee", "Client"],
            "comment" => ""
        ]
        # Format Details: Check https://laraadmin.com/docs/migrations_cruds#schema-ui-types
        
        colname: Database column name. lowercase, words concatenated by underscore (_)
        label: Label of Column e.g. Name, Cost, Is Public
        field_type: It defines type of Column in more General way.
        unique: Whether the column has unique values. Value in true / false
        defaultvalue: Default value for column.
        minlength: Minimum Length of value in integer.
        maxlength: Maximum Length of value in integer.
        required: Is this mandatory field in Add / Edit forms. Value in true / false
        listing_col: Is allowed to show in index page datatable.
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
        if(Schema::hasTable('la_logs')) {
            Schema::drop('la_logs');
        }
    }
}
