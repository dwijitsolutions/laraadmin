<?php
/***
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

use App\Models\BlogPost;
use App\Models\Department;
use App\Models\LAConfig;
use App\Models\LAMenu;
use App\Models\LAModule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Upload;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /* ================ LaraAdmin Seeder Code ================ */

        // Generating Module Menus
        $modules = LAModule::all();
        $dashboardMenu = LAMenu::create([
            'name' => 'Dashboard',
            'url' => '/',
            'icon' => 'fa-home',
            'type' => 'custom',
            'parent' => 0,
            'hierarchy' => 0
        ]);
        $teamMenu = LAMenu::create([
            'name' => 'Team',
            'url' => '#',
            'icon' => 'fa-group',
            'type' => 'custom',
            'parent' => 0,
            'hierarchy' => 1
        ]);
        $blogMenu = LAMenu::create([
            'name' => 'Blog',
            'url' => '#',
            'icon' => 'fa-file-text-o',
            'type' => 'custom',
            'parent' => 0,
            'hierarchy' => 2
        ]);

        foreach ($modules as $module) {
            $parent = 0;
            if ($module->name != 'Backups' && $module->name != 'LALogs') {
                if (in_array($module->name, ['Users', 'Departments', 'Employees', 'Roles', 'Permissions'])) {
                    $parent = $teamMenu->id;
                }
                if (in_array($module->name, ['Blog_posts', 'Blog_categories'])) {
                    $parent = $blogMenu->id;
                }
                LAMenu::create([
                    'name' => $module->name,
                    'url' => $module->name_db,
                    'icon' => $module->fa_icon,
                    'type' => 'module',
                    'parent' => $parent
                ]);
            }
        }

        // Create Administration Department
        $deptAdmin = new Department();
        $deptAdmin->name = 'Administration';
        $deptAdmin->tags = '[]';
        $deptAdmin->color = '#000';
        $deptAdmin->save();

        // Create Customer Department
        $deptCustomer = new Department();
        $deptCustomer->name = 'Customer';
        $deptCustomer->tags = '[]';
        $deptCustomer->color = '#000';
        $deptCustomer->save();

        // Create Super Admin Role
        $roleSA = new Role();
        $roleSA->name = 'SUPER_ADMIN';
        $roleSA->display_name = 'Super Admin';
        $roleSA->description = 'Full Access Role';
        $roleSA->context_type = 'Employee';
        $roleSA->parent = null;
        $roleSA->dept = $deptAdmin->id;
        $roleSA->save();

        // Create Admin Role
        $roleA = new Role();
        $roleA->name = 'ADMIN';
        $roleA->display_name = 'Admin';
        $roleA->description = 'Admin Level Access Role';
        $roleA->context_type = 'Employee';
        $roleA->parent = 1;
        $roleA->dept = $deptAdmin->id;
        $roleA->save();

        // Create Customer Role
        $roleC = new Role();
        $roleC->name = 'CUSTOMER';
        $roleC->display_name = 'Customer';
        $roleC->description = 'Customer Level Access Role';
        $roleC->context_type = 'Customer';
        $roleC->parent = $roleA->id;
        $roleC->dept = $deptCustomer->id;
        $roleC->save();

        // Set Full Access For Super Admin Role
        foreach ($modules as $module) {
            LAModule::setDefaultRoleAccess($module->id, $roleSA->id, 'full');
        }
        // Set Full Access For Super Admin - Menu
        $menus = LAMenu::all();
        foreach ($menus as $menu) {
            $menu->roles()->attach($roleSA->id);
        }
        // Create Admin Panel Permission
        $perm = new Permission();
        $perm->name = 'ADMIN_PANEL';
        $perm->display_name = 'Admin Panel';
        $perm->description = 'Admin Panel Permission';
        $perm->save();

        $roleSA->attachPermission($perm);
        $roleA->attachPermission($perm);

        // Generate LaraAdmin Default Configurations

        $laconfig = new LAConfig();
        $laconfig->section = 'General';
        $laconfig->label = 'Sitename';
        $laconfig->key = 'sitename';
        $laconfig->value = 'LaraAdmin Plus 1.0';
        $laconfig->field_type = 16;
        $laconfig->minlength = 0;
        $laconfig->maxlength = 20;
        $laconfig->required = true;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'General';
        $laconfig->label = 'Sitename First Word';
        $laconfig->key = 'sitename_part1';
        $laconfig->value = 'LaraAdmin';
        $laconfig->field_type = 16;
        $laconfig->minlength = 0;
        $laconfig->maxlength = 20;
        $laconfig->required = false;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'General';
        $laconfig->label = 'Sitename Second Word';
        $laconfig->key = 'sitename_part2';
        $laconfig->value = 'Plus 1.0';
        $laconfig->field_type = 16;
        $laconfig->minlength = 0;
        $laconfig->maxlength = 20;
        $laconfig->required = false;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'General';
        $laconfig->label = 'Sitename Short (2/3 Characters)';
        $laconfig->key = 'sitename_short';
        $laconfig->value = 'LA+';
        $laconfig->field_type = 16;
        $laconfig->minlength = 1;
        $laconfig->maxlength = 3;
        $laconfig->required = true;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'General';
        $laconfig->label = 'Site Description (160 Characters)';
        $laconfig->key = 'site_description';
        $laconfig->value = 'LaraAdmin Plus is a unique Laravel Admin Panel for quick-start Admin based applications and boilerplate for CRM or CMS systems.';
        $laconfig->field_type = 19;
        $laconfig->minlength = 0;
        $laconfig->maxlength = 160;
        $laconfig->required = false;
        $laconfig->save();

        // Display Configurations

        $laconfig = new LAConfig();
        $laconfig->section = 'Display';
        $laconfig->label = 'Navbar Search Box';
        $laconfig->key = 'topbar_search';
        $laconfig->value = false;
        $laconfig->field_type = 2;
        $laconfig->minlength = null;
        $laconfig->maxlength = null;
        $laconfig->required = true;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'Display';
        $laconfig->label = 'Show Navbar Messages';
        $laconfig->key = 'show_messages';
        $laconfig->value = true;
        $laconfig->field_type = 2;
        $laconfig->minlength = null;
        $laconfig->maxlength = null;
        $laconfig->required = true;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'Display';
        $laconfig->label = 'Show Navbar Notifications';
        $laconfig->key = 'show_notifications';
        $laconfig->value = true;
        $laconfig->field_type = 2;
        $laconfig->minlength = null;
        $laconfig->maxlength = null;
        $laconfig->required = true;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'Display';
        $laconfig->label = 'Show Navbar Tasks';
        $laconfig->key = 'show_tasks';
        $laconfig->value = true;
        $laconfig->field_type = 2;
        $laconfig->minlength = null;
        $laconfig->maxlength = null;
        $laconfig->required = true;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'Display';
        $laconfig->label = 'Show Right SideBar';
        $laconfig->key = 'show_rightsidebar';
        $laconfig->value = true;
        $laconfig->field_type = 2;
        $laconfig->minlength = null;
        $laconfig->maxlength = null;
        $laconfig->required = true;
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'Display';
        $laconfig->label = 'Skin / Theme Color';
        $laconfig->key = 'skin';
        $laconfig->value = 'skin-purple';
        $laconfig->field_type = 7;
        $laconfig->minlength = null;
        $laconfig->maxlength = null;
        $laconfig->required = true;
        $laconfig->popup_vals = '["skin-purple", "skin-blue", "skin-black", "skin-yellow", "skin-red", "skin-green", "skin-purple-light", "skin-blue-light", "skin-black-light", "skin-yellow-light", "skin-red-light", "skin-green-light"]';
        $laconfig->save();

        $laconfig = new LAConfig();
        $laconfig->section = 'Display';
        $laconfig->label = 'Layout';
        $laconfig->key = 'layout';
        $laconfig->value = 'fixed';
        $laconfig->field_type = 7;
        $laconfig->minlength = null;
        $laconfig->maxlength = null;
        $laconfig->required = true;
        $laconfig->popup_vals = '["fixed", "sidebar-mini", "fixed-sidebar-mini", "layout-boxed", "layout-top-nav", "sidebar-collapse"]';
        $laconfig->save();

        // Admin Configurations

        $laconfig = new LAConfig();
        $laconfig->section = 'Admin';
        $laconfig->label = 'default_email';
        $laconfig->key = 'default_email';
        $laconfig->value = 'hello@laraadmin.com';
        $laconfig->field_type = 8;
        $laconfig->minlength = 5;
        $laconfig->maxlength = 100;
        $laconfig->required = true;
        $laconfig->save();

        // Module Generation Status

        $modules = LAModule::all();
        foreach ($modules as $module) {
            $module->is_gen = true;
            $module->save();
        }

        // Sample Blog Posts
        $uploadPostBanner = Upload::add('Hello-World-by-Tim-Bogdanov.jpg');

        $post = new BlogPost();
        $post->title = 'Hello World';
        $post->url = 'hello-world';
        $post->category_id = null;
        $post->author_id = null;
        $post->tags = '["Welcome", "LaraAdmin"]';
        $post->post_date = '2017-11-20';
        $post->excerpt = 'Excerpt is a short extract from your post. This can be used as Post Description in Meta Tags.';
        $post->banner = $uploadPostBanner->id;
        $post->content = 'Hello World from LaraAdmin Plus.';
        $post->status = 'Published';
        $post->save();

        // Call for Upload Directory Refresh
        Upload::update_local_upload_paths();

        /* ================ Call Other Seeders ================ */
    }
}
