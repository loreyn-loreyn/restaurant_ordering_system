<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_wrong_role_gets_403(): void
    {
        $cashier = User::where('UserName', 'C002')->firstOrFail();

        $response = $this->actingAs($cashier)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::where('UserName', 'A001')->firstOrFail();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_manager_can_access_manager_routes_but_not_admin(): void
    {
        $manager = User::where('UserName', 'M004')->firstOrFail();

        $this->actingAs($manager)->get('/manager/sales')->assertOk();
        $this->actingAs($manager)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_cashier_can_access_cashier_routes_but_not_kitchen(): void
    {
        $cashier = User::where('UserName', 'C002')->firstOrFail();

        $this->actingAs($cashier)->get('/cashier/order-type')->assertOk();
        $this->actingAs($cashier)->get('/kitchen/orders')->assertForbidden();
    }

    public function test_kitchen_staff_can_access_kitchen_routes_but_not_cashier(): void
    {
        $kitchen = User::where('UserName', 'K003')->firstOrFail();

        $this->actingAs($kitchen)->get('/kitchen/orders')->assertOk();
        $this->actingAs($kitchen)->get('/kitchen/availability')->assertOk();
        $this->actingAs($kitchen)->get('/cashier/order-type')->assertForbidden();
    }

    /**
     * EnsureRole checks role membership only — it does not re-check
     * AccountStatus/AccountApprovalStatus (those are only enforced at
     * login time in Login.php). A deactivated user who is somehow still
     * authenticated (e.g. actingAs in a test, or a session that predates
     * deactivation) is therefore still let through by the middleware
     * alone. This test documents that current behavior rather than
     * assuming the middleware re-checks status.
     */
    public function test_middleware_does_not_itself_recheck_account_status(): void
    {
        $deactivatedCashier = User::where('UserName', 'C005')->firstOrFail();

        $response = $this->actingAs($deactivatedCashier)->get('/cashier/order-type');

        $response->assertOk();
    }
}