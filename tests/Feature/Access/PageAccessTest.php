<?php

namespace Tests\Feature\Access;

use App\Models\BlogCategory;
use App\Models\LAConfig;
use App\Models\LALog;
use App\Models\LAMenu;
use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\Upload;
use Tests\Concerns\ActsAsRoles;
use Tests\TestCase;

/**
 * Opens every page in RouteCatalogue::PAGES as every role and checks who gets in.
 */
class PageAccessTest extends TestCase
{
    use ActsAsRoles;

    /**
     * Module access of each role (super_admin and manager: full, viewer: view only, others: none).
     *
     * @var array<string, array>
     */
    protected const MODULE_ACCESS = [
        'guest' => [], 'customer' => [], 'admin' => [], 'viewer' => ['view'],
        'manager' => ['view', 'create', 'edit', 'delete'], 'super_admin' => ['view', 'create', 'edit', 'delete'],
    ];

    /**
     * Values for the route parameters.
     *
     * @var array<string, string|int>
     */
    protected $params = [];

    /**
     * Fresh DB with one user per role and a record for every record page.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed --class=LaraAdminSeeder');
        $this->createRoleUsers();

        $category = new BlogCategory;
        $category->forceFill(['name' => 'News', 'url' => 'news'])->save();
        $upload = Upload::first();
        $departments = LAModule::get('Departments');

        $this->params = [
            '{post_url}' => 'hello-world',
            '{category_url}' => 'news',
            '{hash}' => $upload->hash,
            '{name}' => $upload->name,
            '{user}' => $this->users['super_admin']->id,
            '{employee}' => $this->users['super_admin']->context_id,
            '{customer}' => $this->users['customer']->context_id,
            '{department}' => 1,
            '{role}' => $this->users['viewer']->roles()->first()->id,
            '{permission}' => 1,
            '{blog_category}' => $category->id,
            '{blog_post}' => 1,
            '{la_log}' => LALog::first()->id,
            '{module_id}' => $departments->id,
            '{la_module}' => $departments->id,
            '{la_module_field}' => LAModuleField::where('module', $departments->id)->first()->id,
            '{la_menu}' => LAMenu::first()->id,
            '{la_config}' => LAConfig::first()->id,
        ];
    }

    /**
     * Every page as every role: allowed users get the page, others are sent to login, refused or sent back to the
     * dashboard. No page may fail.
     *
     * @return void
     */
    public function test_every_page_for_every_role()
    {
        $failures = [];
        foreach (RouteCatalogue::PAGES as $uri => [$group, $module, $access, $okStatus]) {
            $url = '/'.ltrim(strtr($uri, $this->params), '/');
            foreach (array_keys($this->users) as $role) {
                $expected = $this->expected($role, $group, $module, $access);
                $response = $this->asRole($role)->get($url);
                if (! $this->isOutcome($response, $expected, $okStatus)) {
                    $failures[] = sprintf(
                        '%-12s %-45s expected %-9s got %d %s%s',
                        $role,
                        $url,
                        $expected,
                        $response->getStatusCode(),
                        $response->headers->get('Location'),
                        $response->exception ? ' '.get_class($response->exception).': '.$response->exception->getMessage() : ''
                    );
                }
            }
        }

        $this->assertSame([], $failures, "Pages with unexpected results:\n".implode("\n", $failures));
    }

    /**
     * Expected outcome for a role: ok, login (redirect to login), forbidden (403) or denied (back to the dashboard,
     * or a refusal for Ajax endpoints).
     *
     * @return string
     */
    protected function expected($role, $group, $module, $access)
    {
        if ($group === 'public') {
            return 'ok';
        }
        if ($role === 'guest') {
            return 'login';
        }
        if ($role === 'customer') {
            return 'forbidden';
        }
        if ($group === 'sa') {
            return $role === 'super_admin' ? 'ok' : 'forbidden';
        }
        if ($access === null || in_array($access, self::MODULE_ACCESS[$role], true)) {
            return 'ok';
        }

        return 'denied';
    }

    /**
     * Whether a response is the expected outcome.
     *
     * @return bool
     */
    protected function isOutcome($response, $expected, $okStatus)
    {
        $status = $response->getStatusCode();
        $location = (string) $response->headers->get('Location');
        $toDashboard = (bool) preg_match('#/admin/?$#', $location);

        return match ($expected) {
            'ok' => $status === $okStatus && ($status !== 302 || (! $toDashboard && ! str_ends_with($location, '/login'))),
            'login' => $status === 302 && str_ends_with($location, '/login'),
            'forbidden' => $status === 403,
            'denied' => ($status === 302 && $toDashboard) || $status === 403 || $this->isJsonRefusal($response),
        };
    }

    /**
     * Uploads endpoints refuse with HTTP 200 and {"status": "failure", "message": "Unauthorized Access"}, which
     * their JavaScript expects.
     *
     * @return bool
     */
    protected function isJsonRefusal($response)
    {
        $json = json_decode((string) $response->getContent(), true);

        return $response->getStatusCode() === 200 && is_array($json)
            && in_array($json['status'] ?? null, ['failure', 'error'], true)
            && str_contains((string) ($json['message'] ?? ''), 'Unauthori');
    }
}
