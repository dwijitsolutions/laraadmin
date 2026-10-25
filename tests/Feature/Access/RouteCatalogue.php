<?php

namespace Tests\Feature\Access;

/**
 * Every route of the application and the test that covers it. RouteCoverageTest fails when a route is added
 * without being listed here, so new pages and actions get tested for every role.
 */
final class RouteCatalogue
{
    /**
     * GET pages opened by every role in PageAccessTest.
     *
     * uri => [group, module, access, status for an allowed user]
     * - group: "public" (anyone), "panel" (ADMIN_PANEL permission), "sa" (Super Admin only)
     * - module / access: module access the page needs, or null when any panel user may open it
     * - status: 200, or 302 for pages that redirect (Users and LA Logs record pages)
     *
     * @var array<string, array>
     */
    public const PAGES = [
        // Public site
        '/' => ['public', null, null, 200],
        'home' => ['public', null, null, 200],
        'blog' => ['public', null, null, 200],
        'blog/{post_url}' => ['public', null, null, 200],
        'category/{category_url}' => ['public', null, null, 200],
        'files/{hash}/{name}' => ['public', null, null, 200],

        // Admin panel
        'admin' => ['panel', null, null, 200],
        'admin/dashboard' => ['panel', null, null, 200],
        'admin/users' => ['panel', 'Users', 'view', 200],
        'admin/users/{user}' => ['panel', 'Users', 'view', 302],
        'admin/user_dt_ajax' => ['panel', 'Users', 'view', 200],
        'admin/employees' => ['panel', 'Employees', 'view', 200],
        'admin/employees/create' => ['panel', 'Employees', null, 302],
        'admin/employees/{employee}' => ['panel', 'Employees', 'view', 200],
        'admin/employees/{employee}/edit' => ['panel', 'Employees', 'edit', 200],
        'admin/employee_dt_ajax' => ['panel', 'Employees', 'view', 200],
        'admin/customers' => ['panel', 'Customers', 'view', 200],
        'admin/customers/create' => ['panel', 'Customers', null, 302],
        'admin/customers/{customer}' => ['panel', 'Customers', 'view', 200],
        'admin/customers/{customer}/edit' => ['panel', 'Customers', 'edit', 200],
        'admin/customer_dt_ajax' => ['panel', 'Customers', 'view', 200],
        'admin/departments' => ['panel', 'Departments', 'view', 200],
        'admin/departments/create' => ['panel', 'Departments', null, 302],
        'admin/departments/{department}' => ['panel', 'Departments', 'view', 200],
        'admin/departments/{department}/edit' => ['panel', 'Departments', 'edit', 200],
        'admin/department_dt_ajax' => ['panel', 'Departments', 'view', 200],
        'admin/roles' => ['panel', 'Roles', 'view', 200],
        'admin/roles/create' => ['panel', 'Roles', null, 302],
        'admin/roles/{role}' => ['panel', 'Roles', 'view', 200],
        'admin/roles/{role}/edit' => ['panel', 'Roles', 'edit', 200],
        'admin/role_dt_ajax' => ['panel', 'Roles', 'view', 200],
        'admin/hierarchy_view' => ['panel', 'Roles', 'view', 200],
        'admin/permissions' => ['panel', 'Permissions', 'view', 200],
        'admin/permissions/create' => ['panel', 'Permissions', null, 302],
        'admin/permissions/{permission}' => ['panel', 'Permissions', 'view', 200],
        'admin/permissions/{permission}/edit' => ['panel', 'Permissions', 'edit', 200],
        'admin/permission_dt_ajax' => ['panel', 'Permissions', 'view', 200],
        'admin/blog_categories' => ['panel', 'Blog_categories', 'view', 200],
        'admin/blog_categories/create' => ['panel', 'Blog_categories', null, 302],
        'admin/blog_categories/{blog_category}' => ['panel', 'Blog_categories', 'view', 200],
        'admin/blog_categories/{blog_category}/edit' => ['panel', 'Blog_categories', 'edit', 200],
        'admin/blog_category_dt_ajax' => ['panel', 'Blog_categories', 'view', 200],
        'admin/blog_posts' => ['panel', 'Blog_posts', 'view', 200],
        'admin/blog_posts/create' => ['panel', 'Blog_posts', null, 302],
        'admin/blog_posts/{blog_post}' => ['panel', 'Blog_posts', 'view', 200],
        'admin/blog_posts/{blog_post}/edit' => ['panel', 'Blog_posts', 'edit', 200],
        'admin/blog_post_dt_ajax' => ['panel', 'Blog_posts', 'view', 200],
        'admin/uploads' => ['panel', 'Uploads', 'view', 200],
        'admin/uploaded_files' => ['panel', 'Uploads', 'view', 200],
        'admin/backups' => ['panel', 'Backups', 'view', 200],
        'admin/backup_dt_ajax' => ['panel', 'Backups', 'view', 200],
        'admin/la_logs' => ['panel', 'LA_logs', 'view', 200],
        'admin/la_logs/{la_log}' => ['panel', 'LA_logs', 'view', 302],
        'admin/la_log_dt_ajax' => ['panel', 'LA_logs', 'view', 200],
        'admin/quick_add_form/{module_id}' => ['panel', 'Departments', 'create', 200],

        // Super Admin tools
        'admin/la_modules' => ['sa', null, null, 200],
        'admin/la_modules/{la_module}' => ['sa', null, null, 200],
        'admin/la_module_fields/{la_module_field}/edit' => ['sa', null, null, 200],
        'admin/la_menus' => ['sa', null, null, 200],
        'admin/la_menus/{la_menu}' => ['sa', null, null, 200],
        'admin/la_configs' => ['sa', null, null, 200],
        'admin/la_configs/{la_config}/edit' => ['sa', null, null, 200],
        'admin/la_codeeditor' => ['sa', null, null, 200],
    ];

    /**
     * Every other route: "METHOD uri" => the test that exercises it, for the roles that matter.
     *
     * @var array<string, string>
     */
    public const ACTIONS = [
        // Authentication
        'GET login' => AuthTest::class.'::test_login_and_logout_are_logged',
        'POST login' => AuthTest::class.'::test_login_and_logout_are_logged',
        'GET logout' => AuthTest::class.'::test_login_and_logout_are_logged',
        'POST logout' => AuthTest::class.'::test_login_and_logout_are_logged',
        'GET register' => AuthTest::class.'::test_first_user_registers_as_super_admin',
        'POST register' => AuthTest::class.'::test_nobody_can_register_as_super_admin_once_a_user_exists',
        'GET customers/register' => AuthTest::class.'::test_customers_can_register',
        'GET password/reset' => AuthTest::class.'::test_password_reset',
        'POST password/email' => AuthTest::class.'::test_password_reset',
        'GET password/reset/{token}' => AuthTest::class.'::test_password_reset',
        'POST password/reset' => AuthTest::class.'::test_password_reset',
        'GET password/confirm' => AuthTest::class.'::test_password_confirmation',
        'POST password/confirm' => AuthTest::class.'::test_password_confirmation',
        'GET api/user' => AuthTest::class.'::test_api_user_needs_a_token',

        // Public site
        'POST inquiry' => PublicSiteTest::class.'::test_inquiry_form',

        // Module records (store / update / destroy) for every module with a form
        'POST admin/employees' => ModuleFormsTest::class.'::test_employees_form',
        'PUT admin/employees/{employee}' => ModuleFormsTest::class.'::test_employees_form',
        'DELETE admin/employees/{employee}' => ModuleFormsTest::class.'::test_employees_form',
        'POST admin/customers' => ModuleFormsTest::class.'::test_customers_form',
        'PUT admin/customers/{customer}' => ModuleFormsTest::class.'::test_customers_form',
        'DELETE admin/customers/{customer}' => ModuleFormsTest::class.'::test_customers_form',
        'POST admin/departments' => ModuleFormsTest::class.'::test_departments_form',
        'PUT admin/departments/{department}' => ModuleFormsTest::class.'::test_departments_form',
        'DELETE admin/departments/{department}' => ModuleFormsTest::class.'::test_departments_form',
        'POST admin/roles' => ModuleFormsTest::class.'::test_roles_form',
        'PUT admin/roles/{role}' => ModuleFormsTest::class.'::test_roles_form',
        'DELETE admin/roles/{role}' => ModuleFormsTest::class.'::test_roles_form',
        'POST admin/permissions' => ModuleFormsTest::class.'::test_permissions_form',
        'PUT admin/permissions/{permission}' => ModuleFormsTest::class.'::test_permissions_form',
        'DELETE admin/permissions/{permission}' => ModuleFormsTest::class.'::test_permissions_form',
        'POST admin/blog_categories' => ModuleFormsTest::class.'::test_blog_categories_form',
        'PUT admin/blog_categories/{blog_category}' => ModuleFormsTest::class.'::test_blog_categories_form',
        'DELETE admin/blog_categories/{blog_category}' => ModuleFormsTest::class.'::test_blog_categories_form',
        'POST admin/blog_posts' => ModuleFormsTest::class.'::test_blog_posts_form',
        'PUT admin/blog_posts/{blog_post}' => ModuleFormsTest::class.'::test_blog_posts_form',
        'DELETE admin/blog_posts/{blog_post}' => ModuleFormsTest::class.'::test_blog_posts_form',

        // Employees, roles and permissions
        'POST admin/change_password/{id}' => TeamActionsTest::class.'::test_change_password',
        'POST admin/save_module_role_permissions/{id}' => TeamActionsTest::class.'::test_save_module_role_permissions',
        'POST admin/save_permissions/{id}' => TeamActionsTest::class.'::test_save_permissions',
        'POST admin/update_role_hierarchy' => TeamActionsTest::class.'::test_update_role_hierarchy',

        // Uploads
        'POST admin/upload_files' => UploadActionsTest::class.'::test_upload_and_manage_files',
        'POST admin/uploads_update_caption' => UploadActionsTest::class.'::test_upload_and_manage_files',
        'POST admin/uploads_update_filename' => UploadActionsTest::class.'::test_upload_and_manage_files',
        'POST admin/uploads_update_public' => UploadActionsTest::class.'::test_upload_and_manage_files',
        'POST admin/uploads_delete_file' => UploadActionsTest::class.'::test_upload_and_manage_files',
        'GET admin/update_local_upload_paths' => UploadActionsTest::class.'::test_update_local_upload_paths',

        // Backups
        'POST admin/create_backup_ajax' => BackupActionsTest::class.'::test_backups',
        'GET admin/downloadBackup/{id}' => BackupActionsTest::class.'::test_backups',
        'DELETE admin/backups/{backup}' => BackupActionsTest::class.'::test_backups',

        // Logs
        'POST admin/get_lalog_details/{id}' => LogActionsTest::class.'::test_log_details',

        // Generic form helpers
        'POST admin/quick_add_form_save/{module_id}' => FormHelpersTest::class.'::test_quick_add',
        'POST admin/check_unique_val/{field_id}' => FormHelpersTest::class.'::test_check_unique_val',
        'POST admin/checklist_update' => FormHelpersTest::class.'::test_checklist_update',
        'GET admin/find/{module_name}' => FormHelpersTest::class.'::test_search',

        // Module Manager (Super Admin)
        'POST admin/la_modules' => ModuleManagerTest::class.'::test_create_update_and_delete_module',
        'PUT admin/la_modules/{la_module}' => ModuleManagerTest::class.'::test_create_update_and_delete_module',
        'POST admin/la_module_update' => ModuleManagerTest::class.'::test_create_update_and_delete_module',
        'DELETE admin/la_modules/{la_module}' => ModuleManagerTest::class.'::test_create_update_and_delete_module',
        'GET admin/la_modules/{model_id}/set_view_col/{column_name}' => ModuleManagerTest::class.'::test_create_update_and_delete_module',
        'POST admin/la_modules/get_module_files/{module_id}' => ModuleManagerTest::class.'::test_create_update_and_delete_module',
        'POST admin/la_module_fields' => ModuleManagerTest::class.'::test_module_fields',
        'PUT admin/la_module_fields/{la_module_field}' => ModuleManagerTest::class.'::test_module_fields',
        'GET admin/la_module_fields/{id}/delete' => ModuleManagerTest::class.'::test_module_fields',
        'DELETE admin/la_module_fields/{la_module_field}' => ModuleManagerTest::class.'::test_module_fields',
        'POST admin/la_module_fields_listing_show' => ModuleManagerTest::class.'::test_module_fields',
        'GET admin/la_modules/save_module_field_sort/{model_id}' => ModuleManagerTest::class.'::test_module_fields',
        'POST admin/la_modules/save_role_module_permissions/{id}' => ModuleManagerTest::class.'::test_save_role_module_permissions',
        'GET admin/la_modules/generate_crud/{model_id}' => ModuleManagerTest::class.'::test_generate_code',
        'GET admin/la_modules/generate_migr/{model_id}' => ModuleManagerTest::class.'::test_generate_code',
        'GET admin/la_modules/generate_migr_crud/{model_id}' => ModuleManagerTest::class.'::test_generate_code',
        'GET admin/la_modules/generate_update/{model_id}' => ModuleManagerTest::class.'::test_generate_code',
        'GET admin/git-status' => ModuleManagerTest::class.'::test_git_status',

        // Menus and settings (Super Admin)
        'POST admin/la_menus' => MenuAndConfigTest::class.'::test_menus',
        'PUT admin/la_menus/{la_menu}' => MenuAndConfigTest::class.'::test_menus',
        'DELETE admin/la_menus/{la_menu}' => MenuAndConfigTest::class.'::test_menus',
        'POST admin/la_menus/update_hierarchy' => MenuAndConfigTest::class.'::test_menus',
        'POST admin/la_menus_save_role_permissions/{id}' => MenuAndConfigTest::class.'::test_menus',
        'POST admin/la_configs' => MenuAndConfigTest::class.'::test_configs',
        'PUT admin/la_configs/{la_config}' => MenuAndConfigTest::class.'::test_configs',
        'POST admin/la_configs/edit_save/{id}' => MenuAndConfigTest::class.'::test_configs',
        'GET admin/la_configs/ajax_destroy/{id}' => MenuAndConfigTest::class.'::test_configs',
        'DELETE admin/la_configs/{la_config}' => MenuAndConfigTest::class.'::test_configs',
    ];
}
