<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Modules\Companies\Models\Company;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Services\Models\Service;
use App\Modules\Rooms\Models\Room;
use App\Modules\Customers\Models\Customer;
use App\Services\TenantManager;

class TicketCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $user;
    protected Service $service;
    protected Room $room;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create company/tenant
        $this->company = Company::create([
            'name' => 'Test Company',
            'subdomain' => 'test',
            'slug' => 'test',
            'is_active' => true,
        ]);

        $plan = \App\Modules\Subscriptions\Models\Plan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 29.99,
            'billing_period' => 'monthly',
            'is_active' => true,
        ]);

        $this->company->plan_id = $plan->id;
        $this->company->save();

        // Set tenant
        app(TenantManager::class)->setTenant($this->company);

        // Create user/staff
        $this->user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'requires_password_change' => false,
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        // Create role and permission
        $role = Role::create([
            'name' => 'Staff',
            'slug' => 'staff',
            'is_global' => true,
        ]);

        $permission = Permission::create([
            'name' => 'Ticket Cancel',
            'slug' => 'ticket_cancel',
            'is_global' => true,
            'module' => 'queue',
        ]);

        $viewPermission = Permission::create([
            'name' => 'Queue View',
            'slug' => 'queue.view',
            'is_global' => true,
            'module' => 'queue',
        ]);

        $role->permissions()->attach([$permission->id, $viewPermission->id]);
        $this->user->roles()->attach($role);

        // Attach permissions to the plan as well
        $plan->permissions()->attach([$permission->id, $viewPermission->id]);

        // Create service and room
        $this->service = Service::create([
            'company_id' => $this->company->id,
            'name' => 'Test Service',
            'slug' => 'test-service',
            'prefix' => 'T',
            'duration_minutes' => 15,
            'is_active' => true,
        ]);

        $this->room = Room::create([
            'company_id' => $this->company->id,
            'name' => 'Room 1',
            'slug' => 'room-1',
            'is_active' => true,
        ]);

        // Create customer
        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
        ]);

        $this->user->refresh();
    }

    public function test_staff_can_cancel_waiting_ticket_with_predefined_reason(): void
    {
        $ticket = Ticket::create([
            'company_id' => $this->company->id,
            'service_id' => $this->service->id,
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'status' => 'waiting',
            'position' => 1,
            'waited_since' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/queue/{$ticket->id}/cancel", [
                'reason' => 'Customer Left',
                'note' => 'Customer left the waiting area.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $ticket->refresh();
        $this->assertEquals('cancelled', $ticket->status);
        $this->assertEquals('Customer Left', $ticket->cancellation_reason);
        $this->assertEquals('Customer left the waiting area.', $ticket->cancellation_note);
        $this->assertEquals(0, $ticket->position);
        $this->assertEquals($this->user->id, $ticket->cancelled_by);
    }

    public function test_staff_cannot_cancel_serving_ticket(): void
    {
        $ticket = Ticket::create([
            'company_id' => $this->company->id,
            'service_id' => $this->service->id,
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'status' => 'serving',
            'position' => 0,
            'waited_since' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/queue/{$ticket->id}/cancel", [
                'reason' => 'Customer Left',
            ]);

        $response->assertStatus(422);
        
        $ticket->refresh();
        $this->assertEquals('serving', $ticket->status);
    }

    public function test_cancel_requires_valid_predefined_reason(): void
    {
        $ticket = Ticket::create([
            'company_id' => $this->company->id,
            'service_id' => $this->service->id,
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'status' => 'waiting',
            'position' => 1,
            'waited_since' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/queue/{$ticket->id}/cancel", [
                'reason' => 'Invalid Reason',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);

        $ticket->refresh();
        $this->assertEquals('waiting', $ticket->status);
    }
}
