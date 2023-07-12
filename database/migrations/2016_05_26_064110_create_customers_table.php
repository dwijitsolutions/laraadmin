<?php
/***
 * Migration generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

use App\Models\LAModule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Migration generate Module Table Schema by LaraAdmin.
     *
     * @return void
     */
    public function up()
    {
        LAModule::generate('Customers', 'customers', 'name', 'fa-user', [
            [
                'colname' => 'name',
                'label' => 'Name',
                'field_type' => 'Name',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 5,
                'maxlength' => 250,
                'required' => true,
                'listing_col' => true
            ], [
                'colname' => 'designation',
                'label' => 'Designation',
                'field_type' => 'String',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 50,
                'required' => false,
                'listing_col' => true
            ], [
                'colname' => 'organization',
                'label' => 'Organization',
                'field_type' => 'String',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 50,
                'required' => false,
                'listing_col' => true
            ], [
                'colname' => 'gender',
                'label' => 'Gender',
                'field_type' => 'Radio',
                'unique' => false,
                'defaultvalue' => 'Male',
                'minlength' => 0,
                'maxlength' => 0,
                'required' => true,
                'listing_col' => false,
                'popup_vals' => ['Male', 'Female'],
            ], [
                'colname' => 'phone_primary',
                'label' => 'Primary Phone',
                'field_type' => 'Mobile',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 5,
                'maxlength' => 15,
                'required' => true,
                'listing_col' => true
            ], [
                'colname' => 'phone_secondary',
                'label' => 'Secondary Phone',
                'field_type' => 'Mobile',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 5,
                'maxlength' => 15,
                'required' => false,
                'listing_col' => false
            ], [
                'colname' => 'email_primary',
                'label' => 'Primary Email',
                'field_type' => 'Email',
                'unique' => true,
                'defaultvalue' => '',
                'minlength' => 5,
                'maxlength' => 100,
                'required' => true,
                'listing_col' => true
            ], [
                'colname' => 'email_secondary',
                'label' => 'Secondary Email',
                'field_type' => 'Email',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 5,
                'maxlength' => 100,
                'required' => false,
                'listing_col' => false
            ], [
                'colname' => 'profile_img',
                'label' => 'Profile Image',
                'field_type' => 'Image',
                'unique' => false,
                'defaultvalue' => null,
                'required' => false,
                'listing_col' => true
            ], [
                'colname' => 'city',
                'label' => 'City',
                'field_type' => 'String',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 2,
                'maxlength' => 50,
                'required' => false,
                'listing_col' => false
            ], [
                'colname' => 'address',
                'label' => 'Address',
                'field_type' => 'Address',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 4,
                'maxlength' => 1000,
                'required' => false,
                'listing_col' => false
            ], [
                'colname' => 'about',
                'label' => 'About',
                'field_type' => 'String',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 0,
                'required' => false,
                'listing_col' => false
            ], [
                'colname' => 'date_birth',
                'label' => 'Date of Birth',
                'field_type' => 'Date',
                'unique' => false,
                'defaultvalue' => 'NULL',
                'minlength' => 0,
                'maxlength' => 0,
                'required' => false,
                'listing_col' => false
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
        if (Schema::hasTable('customers')) {
            Schema::drop('customers');
        }
    }
}
