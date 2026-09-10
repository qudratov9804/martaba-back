<?php

use App\Enums\RoleName;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function actingAsSuperAdmin(?Organization $organization = null): User
{
    $user = User::factory()->create(['organization_id' => $organization?->id]);
    $user->assignRole(RoleName::SuperAdmin->value);

    return $user;
}

function actingAsTeacher(?Organization $organization = null): User
{
    $user = User::factory()->create(['organization_id' => $organization?->id ?? Organization::factory()]);
    $user->assignRole(RoleName::Teacher->value);

    return $user;
}

function actingAsStudent(?Organization $organization = null): User
{
    $user = User::factory()->create(['organization_id' => $organization?->id ?? Organization::factory()]);
    $user->assignRole(RoleName::Student->value);

    return $user;
}

/**
 * Sanctum's guard caches the resolved user on the guard instance, which
 * persists across sequential requests within a single test (they share
 * the same application container). Without forgetting guards here, a
 * second request authenticated as a different user would silently keep
 * resolving to whichever user was authenticated first.
 *
 * @return array<string, string>
 */
function bearerHeaderFor(User $user): array
{
    app('auth')->forgetGuards();

    return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
}
