# Engineering Debt

Known issues and follow-ups in LaraAdmin 1.13.0 (Laravel 13, PHP 8.4). Unless noted, each item
**predates the Laravel 13 upgrade** and behaves the same on 1.1.0 / Laravel 8. Line numbers refer to
1.13.0.

Each item has: what happens, why, a suggested fix, and the risk of the fix.

## 1. Bugs

### 1.1 Multiselect default changes when a field is saved again

- **What happens:** a Multiselect field created with an empty default gets the column default `[]`.
  Saving the field again in Module Manager (unchanged) alters the column default to `[""]`. New rows
  then start with one empty value instead of none.
- **Why:** `LAModule::generate()` (`app/Models/LAModule.php`, about line 141) stores an empty
  Multiselect/Taginput default in `la_module_fields.defaultvalue` as `json_encode([""])`. But
  `create_field_schema()` (case `Multiselect`, line 604) maps an empty default to `[]`. On re-save, the
  stored `[""]` is applied.
- **Suggested fix:** in `generate()`, store `[]` for an empty default (`''` / `null`), so metadata and
  column agree. Decide whether Taginput should follow. It currently gets `[""]` in both places (line
  747), which is consistent but probably not intended.
- **Risk:** existing installs keep `[""]` in metadata until the field is saved again. Optionally add
  a data fix that rewrites `defaultvalue = '[""]'` to `[]` for these field types.
- **Test:** `tests/Feature/ModuleSchemaTest.php` excludes `multiselect` from the unchanged-save
  comparison. Remove that exclusion once fixed.

### 1.2 Resource routes for controller methods that don't exist (18 routes return 500)

- **What happens:** `GET` to these routes throws "Method ... does not exist" (HTTP 500):

  | Route (under `/admin`) | Missing method |
  |---|---|
  | `backups/create`, `backups/{id}`, `backups/{id}/edit` | `create`, `show`, `edit` |
  | `la_configs/create`, `la_configs/{id}` | `create`, `show` |
  | `la_logs/create`, `la_logs/{id}/edit` | `create`, `edit` |
  | `la_menus/create`, `la_menus/{id}/edit` | `create`, `edit` |
  | `la_module_fields`, `la_module_fields/create`, `la_module_fields/{id}` | `index`, `create`, `show` |
  | `la_modules/create`, `la_modules/{id}/edit` | `create`, `edit` |
  | `uploads/create`, `uploads/{id}`, `uploads/{id}/edit` | `create`, `show`, `edit` |
  | `users/create`, `users/{id}/edit` | `create`, `edit` |

- **Why:** `routes/admin.php` registers full `Route::resource(...)` routes, but these controllers only
  implement some of the actions. Creation happens in modals that post to `store`.
- **Suggested fix:** limit each resource to the implemented actions, e.g.
  `Route::resource(...)->only(['index', 'store', 'update', 'destroy'])`, so missing actions return
  404 instead of 500. Check each controller's actual methods first. `AdminSmokeTest` can then assert
  404s.
- **Risk:** low. It removes routes that never worked. Search views for `route('admin.<x>.create')`
  and similar named-route references before removing names.

### 1.3 Blog post page shows a `gallery` field that the module doesn't have

- **What happens:** `/admin/blog_posts/{id}` returns 500 with `Undefined array key "gallery"`.
- **Why:** `resources/views/la/blog_posts/show.blade.php:127` has `@la_display($module, 'gallery')`, but
  the Blog_posts module (`database/migrations/2017_11_20_063206_create_blog_posts_table.php`) has no
  `gallery` field. `LAFormMaker::display()` (`app/Helpers/LAFormMaker.php:1228`) indexes
  `$module->fields[$field_name]` without checking that it exists.
- **Suggested fix:** remove the line (or add the field to the module). Optionally make
  `LAFormMaker::display()` and `input()` return an empty string or throw a clear exception for unknown
  fields.

### 1.4 Blog category page crashes for an unknown category

- **What happens:** `/category/{url}` returns 500 (`Attempt to read property "id" on null`) when no
  category matches.
- **Why:** `BlogPostsController::show_category()` (`app/Http/Controllers/LA/BlogPostsController.php:438`)
  uses `$category->id` in a query before its `isset($category->id)` check.
- **Suggested fix:** use `BlogCategory::where('url', $url)->firstOrFail()` (404), or move the check up.
  Look for the same pattern in `show_post()`.

### 1.5 Module search (`/admin/find/{module}`) cannot work

- **What happens:** 500 for any module name. The admin header's search box calls this route
  (`resources/views/la/layouts/partials/mainheader.blade.php:74`), so header search is broken.
- **Why:** `SearchController::find()` (`app/Http/Controllers/LA/SearchController.php`):
  - dereferences `LAModule::get()` without a null check;
  - looks up `Roles`/`Users`/`Permissions` models in `\App\` instead of `\App\Models\`;
  - calls `Model::search()`, which needs Laravel Scout. Scout isn't installed and no model uses
    `Searchable`. The models do define `searchableAs()`.
- **Suggested fix:** decide whether to support search. Either add `laravel/scout` with a driver and
  the `Searchable` trait (plus fix the namespace and null check), or remove the route, controller and
  `searchableAs()` methods together with the header search box.

### 1.6 Password confirmation page has no layout

- **What happens:** `/password/confirm` returns 500 (`View [layouts.app] not found`).
- **Why:** `resources/views/auth/passwords/confirm.blade.php` comes from laravel/ui scaffolding and
  extends `layouts.app`, which LaraAdmin doesn't ship. The other auth views use LaraAdmin's own layout.
- **Suggested fix:** port the view to LaraAdmin's auth layout (as `auth/login.blade.php` does), or
  remove the route if `password.confirm` isn't used.

### 1.7 `la:crud` doesn't grant Super Admin access to the generated module

- **What happens:** after `php artisan la:crud Foo`, the module's pages redirect for Super Admin until
  access is granted in Roles. Module Manager's "Generate CRUD" grants it automatically.
- **Why:** `LAModuleController::generate_crud()` (line 166) and `generate_migr_crud()` (line 226) call
  `LAModule::setDefaultRoleAccess($module->id, $superAdminRoleId, 'full')`. `App\Console\Commands\Crud`
  doesn't.
- **Suggested fix:** make the command do the same, or move the grant into a shared CodeGenerator step.
  `CrudGeneratorTest` grants access manually; drop that once fixed.

### 1.8 Module deletion leaves log-type config behind

- **What happens:** after deleting a module, its `*_CREATED/UPDATED/DELETED` entries remain in
  `config/laraadmin.php` under `log`.
- **Why:** `CodeGenerator::appendRoutes()` appends them, but `LAModule::deleteModule()` doesn't remove
  them.
- **Suggested fix:** remove the module's block (the generated block between its `// <Module>` comment
  and the next entry) in `deleteModule()`. Make sure the `// More LALogs Configurations - Do not edit
  this line` marker stays intact.

## 2. Security

### 2.1 State-changing actions on GET routes

- **What happens:** these GET routes change data or files, so they bypass CSRF protection. A
  logged-in Super Admin who opens a crafted link or image URL triggers them. They also run during
  any automated crawl.

  | Route (under `/admin`) | Effect |
  |---|---|
  | `la_modules/generate_crud/{id}`, `generate_migr/{id}`, `generate_update/{id}`, `generate_migr_crud/{id}` | Regenerate controller, model, views and migration files |
  | `la_module_fields/{id}/delete` | Drops a column and its field |
  | `la_configs/ajax_destroy/{id}` | Deletes a setting |
  | `la_modules/{id}/set_view_col/{column}` | Changes a module's view column |
  | `la_modules/save_module_field_sort/{id}` | Reorders fields |
  | `update_local_upload_paths` | Rewrites upload paths |
  | `git-status` | Runs `git log` via `exec()` and prints it |

- **Why:** historical design in `routes/admin.php` (lines 34–74, 123), with the JS calling these URLs
  via GET.
- **Suggested fix:** change them to POST/DELETE (with the CSRF token, which `VerifyCsrfToken` /
  `PreventRequestForgery` already checks). Update the matching AJAX calls in the Module Manager, menu
  and config views. Remove `git-status` or replace it with a read-only version that doesn't shell out.
- **Risk:** medium. Each JS caller must be updated together with its route.

### 2.2 Cache unserialization is unrestricted

- **What:** `config/cache.php` sets `serializable_classes` to `null` (all classes allowed). Laravel 13's
  default is `false`, which hardens against gadget chains if `APP_KEY` leaks.
- **Why kept:** on taggable stores (Redis, Memcached), Entrust caches Eloquent role and permission
  collections.
- **Suggested fix:** list the needed classes (`Illuminate\Database\Eloquent\Collection`,
  `App\Models\Role`, `App\Models\Permission`, Carbon) and test Entrust with Redis.

### 2.3 Sessions use PHP serialization

- **What:** `config/session.php` keeps `'serialization' => 'php'` so sessions survive the upgrade.
  Laravel 13 recommends `json`.
- **Suggested fix:** switch to `json` in a later release. This logs everyone out once.

## 3. Decisions pending

### 3.1 Currency column type

- Since Laravel 11, `double()` takes no precision arguments, so new Currency columns are `DOUBLE`
  instead of `DOUBLE(15,2)` (`app/Models/LAModule.php`, case `Currency`, line 316). Existing columns
  are unchanged until the field is edited.
- **Option:** use `decimal(15, 2)`, which is exact for money. Editing an existing Currency field would
  then convert its column from DOUBLE to DECIMAL.

### 3.2 Changing a field's type to Integer makes the column NOT NULL

- Since Laravel 11, `->change()` restates the full column. A type change to Integer (case `Integer`,
  line 554) produces `NOT NULL`, the same as creating an Integer field. Laravel 8 kept the old
  nullability.
- **Risk:** if existing rows have NULL in that column, the `ALTER` fails under MySQL strict mode.
- **Option:** for type changes, keep the current nullability or make Integer fields nullable when not
  required. Alternatively, have Module Manager warn when NULLs exist.

## 4. Verification gaps from the upgrade

These areas are not covered by automated tests or the HTTP crawl:

- **In-browser behaviour:** DataTables rendering, select2, the upload dropzone, menu drag and drop
  (nestable), Module Manager modals and field sorting, pjax navigation. Server responses are tested;
  the JavaScript running against them isn't.
- **Backups:** creating and downloading a backup (`BackupsController::create_backup_ajax` →
  `backup:run`) with spatie/laravel-backup 10. It needs `mysqldump` on the host. The app has no
  `config/backup.php`, so package defaults apply. Publish and review it.
- **File uploads:** upload, public/private toggle and `files/{hash}/{name}` downloads.
- **Mail:** employee/customer credential mails and the inquiry form through a real mailer (Symfony
  Mailer replaced SwiftMailer in Laravel 9).
- **MariaDB:** only MySQL 8.0 was tested. JSON field defaults use an expression default (needs MySQL
  8.0.13+ / MariaDB 10.2+).

## 5. Maintenance

- **Front-end toolchain:** Laravel Mix 6 and `axios ^0.21` (old, has advisories) in `package.json`.
  Compiled assets in `public/` and `public/la-assets/` are committed. Consider Vite, or at least
  update the dependencies and rebuild.
- **Tests are slow:** every test runs `migrate:refresh` and seeds in `setUp`. Consider
  `RefreshDatabase` with a seeded schema, or a shared migrated database per run.
- **No CI:** the README's Travis badge is dead. Add a GitHub Actions workflow running PHPUnit (MySQL
  service) and `composer sniff` on PHP 8.3 and 8.4.
- **Legacy code:**
  - `LAHelper::laravel_ver()` is deprecated. Remove it once no site or package (e.g.
    `laraadmin/editor`) calls it.
  - `LAProvider::boot()` still carries commented-out package-era publishing code.
  - The root `server.php` isn't used by Laravel 11+ `artisan serve`.
- **Application structure:** LaraAdmin keeps the Laravel 8 layout (`app/Http/Kernel.php`,
  `app/Console/Kernel.php`, `app/Exceptions/Handler.php`, classic `bootstrap/app.php`). Laravel 13
  supports it, but the newer `bootstrap/app.php` style is now the default and may become required in a
  future major.
- **README:** the heading and docs links still say "LaraAdmin 1.1".
