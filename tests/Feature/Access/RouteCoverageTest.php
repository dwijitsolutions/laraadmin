<?php

namespace Tests\Feature\Access;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Every application route is listed in RouteCatalogue, with a test that exists.
 */
class RouteCoverageTest extends TestCase
{
    /**
     * Routes registered by packages, not by LaraAdmin.
     *
     * @var array<int, string>
     */
    protected const PACKAGE_ROUTES = ['GET sanctum/csrf-cookie', 'GET storage/{path}', 'PUT storage/{path}', 'GET up'];

    /**
     * Every route is in the catalogue, and the catalogue has no stale entries.
     *
     * @return void
     */
    public function test_every_route_is_covered()
    {
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            // GET|HEAD is listed as GET, PUT|PATCH as PUT
            $key = $route->methods()[0].' '.$route->uri();
            if (! in_array($key, self::PACKAGE_ROUTES, true)) {
                $routes[] = $key;
            }
        }

        $catalogue = array_merge(
            array_map(fn ($uri) => 'GET '.$uri, array_keys(RouteCatalogue::PAGES)),
            array_keys(RouteCatalogue::ACTIONS)
        );

        $this->assertSame([], array_values(array_diff($routes, $catalogue)), 'Routes missing from RouteCatalogue');
        $this->assertSame([], array_values(array_diff($catalogue, $routes)), 'RouteCatalogue lists routes that do not exist');
    }

    /**
     * Every test named in the catalogue exists.
     *
     * @return void
     */
    public function test_catalogue_names_existing_tests()
    {
        $missing = [];
        foreach (array_unique(RouteCatalogue::ACTIONS) as $test) {
            [$class, $method] = explode('::', $test);
            if (! method_exists($class, $method)) {
                $missing[] = $test;
            }
        }

        $this->assertSame([], $missing);
    }
}
