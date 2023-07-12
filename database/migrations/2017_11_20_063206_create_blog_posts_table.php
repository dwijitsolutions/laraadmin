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

class CreateBlogpostsTable extends Migration
{
    /**
     * Migration generate Module Table Schema by LaraAdmin.
     *
     * @return void
     */
    public function up()
    {
        LAModule::generate('Blog_posts', 'blog_posts', 'title', 'fa-file-text-o', [
            [
                'colname' => 'title',
                'label' => 'Title',
                'field_type' => 'Name',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 5,
                'maxlength' => 100,
                'required' => true,
                'listing_col' => true
            ], [
                'colname' => 'url',
                'label' => 'URL',
                'field_type' => 'String',
                'unique' => true,
                'defaultvalue' => '',
                'minlength' => 5,
                'maxlength' => 150,
                'required' => true,
                'listing_col' => false
            ], [
                'colname' => 'category_id',
                'label' => 'Category',
                'field_type' => 'Dropdown',
                'unique' => false,
                'defaultvalue' => null,
                'required' => false,
                'listing_col' => true,
                'popup_vals' => '@blog_categories'
            ], [
                'colname' => 'status',
                'label' => 'Status',
                'field_type' => 'Dropdown',
                'unique' => false,
                'defaultvalue' => 'Draft',
                'required' => true,
                'listing_col' => true,
                'popup_vals' => ['Draft', 'Published']
            ], [
                'colname' => 'author_id',
                'label' => 'Author',
                'field_type' => 'Dropdown',
                'unique' => false,
                'defaultvalue' => null,
                'required' => true,
                'listing_col' => true,
                'popup_vals' => '@employees'
            ], [
                'colname' => 'tags',
                'label' => 'Tags',
                'field_type' => 'Taginput',
                'unique' => false,
                'defaultvalue' => [],
                'required' => false,
                'listing_col' => true
            ], [
                'colname' => 'post_date',
                'label' => 'Date',
                'field_type' => 'Date',
                'unique' => false,
                'defaultvalue' => null,
                'required' => true,
                'listing_col' => true
            ], [
                'colname' => 'excerpt',
                'label' => 'Excerpt',
                'field_type' => 'String',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 200,
                'required' => false,
                'listing_col' => false
            ], [
                'colname' => 'banner',
                'label' => 'Banner',
                'field_type' => 'Image',
                'unique' => false,
                'defaultvalue' => null,
                'required' => false,
                'listing_col' => false
            ], [
                'colname' => 'content',
                'label' => 'Content',
                'field_type' => 'HTML',
                'unique' => false,
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 10000,
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
        if (Schema::hasTable('blog_posts')) {
            Schema::drop('blog_posts');
        }
    }
}
