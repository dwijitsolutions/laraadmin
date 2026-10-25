# Engineering Debt

Known issues and follow-ups in LaraAdmin 1.13 (Laravel 13, PHP 8.4). Unless noted, each item
**predates the Laravel 13 upgrade** and behaves the same on 1.1.0 / Laravel 8. Line numbers refer to
1.13.0 for items 1.1–3.2 and to 1.13.1 for later ones. Items fixed in 1.13.1 keep their number, marked as fixed.

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

### 1.2 Resource routes for controller methods that don't exist (fixed in 1.13.1)

`routes/admin.php` limits each resource to the actions its controller implements; the routes that returned 500 no
longer exist. Generated controllers' empty `create()` now redirects to the module's index page.

### 1.3 Blog post page shows a `gallery` field that the module doesn't have (fixed in 1.13.1)

The line is removed, and `LAFormMaker::display()` / `input()` render nothing for a field the module doesn't have.

### 1.4 Blog category page crashes for an unknown category (fixed in 1.13.1)

Unknown blog categories and posts return 404; draft posts are not public.

### 1.5 Module search (`/admin/find/{module}`) cannot work (partly fixed in 1.13.1)

`SearchController::find()` no longer crashes: it returns 404 for unknown modules and for modules whose model doesn't
use Laravel Scout's `Searchable`, and 403 without view access. Search itself still needs Scout: add
`laravel/scout`, a driver and the `Searchable` trait to the models to enable the navbar search box.

### 1.6 Password confirmation page has no layout (fixed in 1.13.1)

`auth/passwords/confirm.blade.php` uses LaraAdmin's auth layout.

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

### 1.9 Required fields are only validated when they are sent

- **What happens:** `LAModule::validateRules()` (`app/Models/LAModule.php`) adds rules only for fields present in
  the request. Leaving a required field out of the request skips its `required` rule, so a record can be added
  without it (for example quick add of a Department without a name).
- **Why not fixed in 1.13.1:** sites may have customised Add forms that leave a required field out on purpose (and
  rely on the column default). Enforcing it could break them in a patch release.
- **Suggested fix:** when adding a record, require every required field the user may write
  (`hasFieldAccess(..., 'write')`). Check the sites' Add forms first.

### 1.10 Customer edit form's "Create user" checkbox does nothing

- **What happens:** `CustomersController::update()` ignores `create_user`; a login user can only be created when the
  customer is added.
- **Suggested fix:** share the user creation code of `store()` and run it from `update()` when the customer has no
  user yet.

### 1.11 Field access uses only the first role that grants module access

- **What happens:** `LAModule::hasFieldAccess()` stops at the first of the user's roles with access to the module
  and uses that role's field access, even if another of the user's roles allows more.
- **Suggested fix:** check the field access of every role that grants module access and return the highest.

### 1.12 Bugs found while migrating sites to 1.13

Sites carry their own fixes for these until they are fixed here:

- The HTML field type is `VARCHAR(10000)`. Several HTML fields in one table exceed MySQL's row size in utf8mb4, and
  converting existing `TEXT` content would truncate it. Use `TEXT` (as LaraAdmin Plus did).
- `CodeGenerator::appendRoutes()` / `appendObservers()` and the migration lookup in `generateMigration()` decide
  "already present" by substring: generating `Categories` or `Posts` is skipped when `BlogCategories` /
  `BlogPosts` exist, and `Page` matches the "Ajax Page Load" comment in `LAProvider`.

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

### 2.4 Users who manage employees can give any role but Super Admin

- **What:** a role with create / edit access to Employees can assign any Employee role, including roles with more
  access than its own (1.13.1 already blocks `SUPER_ADMIN`).
- **Suggested fix:** only offer and accept roles at or below the acting user's roles in the role hierarchy.

### 2.5 Generated passwords are written to the log

- **What:** when mail isn't configured (`LAHelper::is_mail()`), adding an employee or customer and changing an
  employee's password write the plain password to the log (`Log::info(... Password: ...)`).
- **Suggested fix:** show the password once to the admin instead, or send a password reset link; never log it.

### 2.6 "Generate Migration + CRUD" gives every role full access

- **What:** `LAModuleController::generate_migr_crud()` gives every role, Customers included, full access to the new
  module and its menu. "Generate CRUD" gives it to Super Admin only.
- **Suggested fix:** grant Super Admin only, as `generate_crud()` does, and let the admin grant other roles.

### 2.7 Uploads accept any file type

- **What:** `UploadsController::upload_files()` has no type or size validation (the rules are commented out). Since
  1.13.1, HTML, SVG and XML files are always downloaded rather than shown inline, and files can't be renamed out of
  `storage/uploads`.
- **Suggested fix:** validate against a configurable list of extensions and a maximum size.

### 2.8 Entrust doesn't clear its cache when roles or permissions change

- **What:** on taggable caches (Redis, Memcached) laraadmin/entrust caches each user's roles and each role's
  permissions, but `attachRole()` / `detachRole()` / `attachPermission()` / `detachPermission()` don't clear them.
- **Workaround in 1.13.1:** LaraAdmin calls `LAHelper::clearAccessCache()` after each change it makes.
- **Suggested fix:** clear the cache in those Entrust methods (laraadmin/entrust 7.0.1), then drop the workaround.

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
- **Mail:** credential mails through a real mailer (Symfony Mailer replaced SwiftMailer in Laravel 9). Tests use
  the array mailer.
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
