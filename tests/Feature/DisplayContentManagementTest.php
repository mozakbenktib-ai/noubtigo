<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Displays\Models\DisplayContent;
use App\Modules\Displays\Models\DisplayDevice;
use App\Modules\Displays\Services\DisplayContentService;
use App\Modules\Subscriptions\Models\Plan;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Company $companyA;
    protected Company $companyB;
    protected User $adminA;
    protected DisplayContentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 99.00,
            'annual_price' => 990.00,
            'is_active' => true,
        ]);

        // 1. Company A
        $this->companyA = Company::create([
            'name' => 'Company A Clinic',
            'subdomain' => 'companya',
            'slug' => 'companya',
            'is_active' => true,
            'plan_id' => $plan->id,
            'timezone' => 'UTC',
            'secure_public_token' => 'token-comp-a',
        ]);

        // 2. Company B
        $this->companyB = Company::create([
            'name' => 'Company B Pharmacy',
            'subdomain' => 'companyb',
            'slug' => 'companyb',
            'is_active' => true,
            'plan_id' => $plan->id,
            'timezone' => 'UTC',
            'secure_public_token' => 'token-comp-b',
        ]);

        // Set tenant context to Company A
        app(TenantManager::class)->setTenant($this->companyA);

        // Permissions & Admin User
        $permView = Permission::firstOrCreate(['slug' => 'displays.view'], ['name' => 'View Displays', 'module' => 'displays', 'is_global' => true]);
        $permCreate = Permission::firstOrCreate(['slug' => 'displays.create'], ['name' => 'Create Displays', 'module' => 'displays', 'is_global' => true]);
        $permContentView = Permission::firstOrCreate(['slug' => 'display_content.view'], ['name' => 'View Display Content', 'module' => 'display_content', 'is_global' => true]);
        $permContentCreate = Permission::firstOrCreate(['slug' => 'display_content.create'], ['name' => 'Create Display Content', 'module' => 'display_content', 'is_global' => true]);
        $permContentEdit = Permission::firstOrCreate(['slug' => 'display_content.edit'], ['name' => 'Edit Display Content', 'module' => 'display_content', 'is_global' => true]);
        $permContentDelete = Permission::firstOrCreate(['slug' => 'display_content.delete'], ['name' => 'Delete Display Content', 'module' => 'display_content', 'is_global' => true]);

        $allPermIds = [$permView->id, $permCreate->id, $permContentView->id, $permContentCreate->id, $permContentEdit->id, $permContentDelete->id];
        $plan->permissions()->sync($allPermIds);

        $this->adminA = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@companya.com',
            'password' => bcrypt('password'),
            'requires_password_change' => false,
            'company_id' => $this->companyA->id,
            'is_system_admin' => false,
        ]);

        $role = Role::firstOrCreate(['slug' => 'company-admin'], ['name' => 'Company Admin', 'is_global' => true]);
        $role->permissions()->sync($allPermIds);
        $this->adminA->roles()->sync([$role->id]);

        $this->service = app(DisplayContentService::class);
    }

    /**
     * Requirement 33: Test exact scenario specified in user requirements.
     */
    public function test_requirement_33_assignment_and_visibility_scenario(): void
    {
        // Company A Displays
        $dispReception = DisplayDevice::create([
            'company_id' => $this->companyA->id,
            'name' => 'Reception',
            'show_type' => 'both',
            'is_active' => true,
        ]);

        $dispWaiting = DisplayDevice::create([
            'company_id' => $this->companyA->id,
            'name' => 'Waiting Room',
            'show_type' => 'both',
            'is_active' => true,
        ]);

        $dispPharmacy = DisplayDevice::create([
            'company_id' => $this->companyA->id,
            'name' => 'Pharmacy',
            'show_type' => 'both',
            'is_active' => true,
        ]);

        // Company B Display
        $dispCompanyB = DisplayDevice::create([
            'company_id' => $this->companyB->id,
            'name' => 'Display B Main',
            'show_type' => 'both',
            'is_active' => true,
        ]);

        // Content A: Welcome -> All Displays
        $contentA = DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Welcome',
            'type' => 'information',
            'target_type' => 'all',
            'sort_order' => 1,
            'duration' => 10,
            'is_active' => true,
        ]);

        // Content B: Follow your ticket -> All Displays
        $contentB = DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Follow your ticket',
            'type' => 'qr_tracking',
            'target_type' => 'all',
            'sort_order' => 2,
            'duration' => 12,
            'is_active' => true,
        ]);

        // Content C: Pharmacy promotion -> Pharmacy only
        $contentC = DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Pharmacy promotion',
            'type' => 'promotion',
            'target_type' => 'selected',
            'sort_order' => 3,
            'duration' => 15,
            'is_active' => true,
        ]);
        $contentC->devices()->sync([$dispPharmacy->id]);

        // Content D: Waiting room information -> Waiting Room only
        $contentD = DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Waiting room information',
            'type' => 'information',
            'target_type' => 'selected',
            'sort_order' => 4,
            'duration' => 10,
            'is_active' => true,
        ]);
        $contentD->devices()->sync([$dispWaiting->id]);

        // Assertions for Reception: Welcome + Follow your ticket
        $receptionContent = $this->service->getActiveContentForDisplay($dispReception, $this->companyA->id);
        $receptionTitles = $receptionContent->pluck('title')->toArray();
        $this->assertEquals(['Welcome', 'Follow your ticket'], $receptionTitles);
        $this->assertNotContains('Pharmacy promotion', $receptionTitles);
        $this->assertNotContains('Waiting room information', $receptionTitles);

        // Assertions for Waiting Room: Welcome + Follow your ticket + Waiting room information
        $waitingContent = $this->service->getActiveContentForDisplay($dispWaiting, $this->companyA->id);
        $waitingTitles = $waitingContent->pluck('title')->toArray();
        $this->assertEquals(['Welcome', 'Follow your ticket', 'Waiting room information'], $waitingTitles);
        $this->assertNotContains('Pharmacy promotion', $waitingTitles);

        // Assertions for Pharmacy: Welcome + Follow your ticket + Pharmacy promotion
        $pharmacyContent = $this->service->getActiveContentForDisplay($dispPharmacy, $this->companyA->id);
        $pharmacyTitles = $pharmacyContent->pluck('title')->toArray();
        $this->assertEquals(['Welcome', 'Follow your ticket', 'Pharmacy promotion'], $pharmacyTitles);
        $this->assertNotContains('Waiting room information', $pharmacyTitles);

        // Critical Tenant Isolation: Company B must see NONE of Company A's content
        $companyBContent = $this->service->getActiveContentForDisplay($dispCompanyB, $this->companyB->id);
        $this->assertCount(0, $companyBContent);
    }

    /**
     * Test multi-tenant isolation: server-side validation strips foreign device IDs.
     */
    public function test_tenant_isolation_prevents_assigning_other_company_displays(): void
    {
        $dispCompanyB = DisplayDevice::create([
            'company_id' => $this->companyB->id,
            'name' => 'Foreign Display',
            'show_type' => 'both',
            'is_active' => true,
        ]);

        $content = DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Tenant Safe Test',
            'type' => 'information',
            'target_type' => 'selected',
            'is_active' => true,
        ]);

        // Attempt to sync Company B's display ID under Company A's context
        $this->service->syncDisplayAssignments($content, [$dispCompanyB->id], $this->companyA->id);

        // The foreign device ID must NOT be assigned
        $this->assertCount(0, $content->devices);
    }

    /**
     * Test scheduling and active status filtering.
     */
    public function test_scheduling_and_active_filters(): void
    {
        // 1. Inactive item
        DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Inactive Item',
            'type' => 'information',
            'target_type' => 'all',
            'is_active' => false,
        ]);

        // 2. Future scheduled item
        DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Future Scheduled Item',
            'type' => 'announcement',
            'target_type' => 'all',
            'is_active' => true,
            'starts_at' => now('UTC')->addDays(2),
        ]);

        // 3. Expired item
        DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Expired Item',
            'type' => 'promotion',
            'target_type' => 'all',
            'is_active' => true,
            'starts_at' => now('UTC')->subDays(5),
            'ends_at' => now('UTC')->subDay(),
        ]);

        // 4. Currently valid active item
        DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Currently Valid Item',
            'type' => 'information',
            'target_type' => 'all',
            'is_active' => true,
            'starts_at' => now('UTC')->subHour(),
            'ends_at' => now('UTC')->addHour(),
        ]);

        $activeContent = $this->service->getActiveContentForDisplay(null, $this->companyA->id);
        $titles = $activeContent->pluck('title')->toArray();

        $this->assertEquals(['Currently Valid Item'], $titles);
    }

    /**
     * Test CRUD controller endpoints for display contents.
     */
    public function test_admin_crud_workflow(): void
    {
        $this->actingAs($this->adminA);

        // 1. Create content via POST
        $response = $this->post(route('displays.contents.store'), [
            'title' => 'New Promo Feature',
            'description' => 'Great discounts today',
            'type' => 'promotion',
            'target_type' => 'all',
            'duration' => 15,
            'sort_order' => 5,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('displays.contents.index'));
        $this->assertDatabaseHas('display_contents', [
            'company_id' => $this->companyA->id,
            'title' => 'New Promo Feature',
            'duration' => 15,
        ]);

        $content = DisplayContent::where('title', 'New Promo Feature')->firstOrFail();

        // 2. Toggle active
        $this->post(route('displays.contents.toggle', $content->id));
        $this->assertFalse($content->fresh()->is_active);

        // 3. Duplicate
        $this->post(route('displays.contents.duplicate', $content->id));
        $this->assertDatabaseHas('display_contents', [
            'company_id' => $this->companyA->id,
            'title' => 'New Promo Feature (Copy)',
        ]);

        // 4. Delete
        $this->delete(route('displays.contents.destroy', $content->id));
        $this->assertSoftDeleted('display_contents', ['id' => $content->id]);
    }

    /**
     * Test TV display views receive active contents.
     */
    public function test_display_screens_receive_contents_via_tv_views(): void
    {
        // Create an authorized device for Company A
        $device = DisplayDevice::create([
            'company_id' => $this->companyA->id,
            'name' => 'TV Waiting Screen',
            'show_type' => 'both',
            'device_token' => 'test-tv-token-12345',
            'paired_at' => now(),
            'is_active' => true,
        ]);

        // Create content
        DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Signage Live Welcome',
            'type' => 'information',
            'target_type' => 'all',
            'duration' => 10,
            'is_active' => true,
        ]);

        // Access TV view with token
        $response = $this->get(route('queue.display.show', ['token' => $device->device_token]));
        $response->assertStatus(200);
        $response->assertSee('Signage Live Welcome');
        $response->assertSee('data-duration="10"', false);

        // Access JSON endpoint for polling
        $jsonResponse = $this->getJson(route('queue.display.show', ['token' => $device->device_token]));
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonStructure(['active', 'waiting', 'contents_count']);
        $this->assertEquals(1, $jsonResponse->json('contents_count'));
    }

    /**
     * Test reordering display contents.
     */
    public function test_reordering_display_contents(): void
    {
        $this->actingAs($this->adminA);

        $item1 = DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Item 1',
            'type' => 'information',
            'target_type' => 'all',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item2 = DisplayContent::create([
            'company_id' => $this->companyA->id,
            'title' => 'Item 2',
            'type' => 'information',
            'target_type' => 'all',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('displays.contents.reorder'), [
            'orders' => [
                $item1->id => 10,
                $item2->id => 5,
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(10, $item1->fresh()->sort_order);
        $this->assertEquals(5, $item2->fresh()->sort_order);
    }
}

