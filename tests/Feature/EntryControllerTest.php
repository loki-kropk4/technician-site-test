<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customers_index_only_shows_their_own_entries(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);

        $mine = Entry::factory()->create(['customer_id' => $customer->id]);
        Entry::factory()->create(['customer_id' => $other->id]);

        $response = $this->actingAs($customer)->get(route('entries.index'));

        $entries = $response->viewData('entries');
        $this->assertCount(1, $entries);
        $this->assertSame($mine->entry_id, $entries->first()->entry_id);
    }

    public function test_a_technicians_index_shows_every_customers_entries(): void
    {
        $technician = User::factory()->technician()->create();
        Entry::factory()->count(3)->create();

        $response = $this->actingAs($technician)->get(route('entries.index'));

        $this->assertCount(3, $response->viewData('entries'));
    }

    public function test_the_admins_index_shows_every_customers_entries(): void
    {
        $admin = User::factory()->admin()->create();
        Entry::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('entries.index'));

        $this->assertCount(3, $response->viewData('entries'));
    }

    public function test_a_customer_can_view_their_own_entry(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $entry = Entry::factory()->create(['customer_id' => $customer->id, 'problem' => 'Screen is cracked.']);

        $response = $this->actingAs($customer)->get(route('entries.show', $entry));

        $response->assertOk();
        $response->assertSee($entry->entry_id);
        $response->assertSee($entry->name_unit);
        $response->assertSee('Screen is cracked.');
    }

    public function test_a_customer_can_not_view_another_customers_entry(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);
        $entry = Entry::factory()->create(['customer_id' => $other->id]);

        $this->actingAs($customer)->get(route('entries.show', $entry))->assertForbidden();
    }

    public function test_a_technician_and_an_admin_can_view_any_entry(): void
    {
        $technician = User::factory()->technician()->create();
        $admin = User::factory()->admin()->create();
        $entry = Entry::factory()->create();

        $this->actingAs($technician)->get(route('entries.show', $entry))->assertOk();
        $this->actingAs($admin)->get(route('entries.show', $entry))->assertOk();
    }

    public function test_a_guest_is_redirected_to_login_from_the_show_page(): void
    {
        $entry = Entry::factory()->create();

        $this->get(route('entries.show', $entry))->assertRedirect('/login');
    }

    public function test_edit_and_delete_buttons_on_the_show_page_are_staff_only(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $technician = User::factory()->technician()->create();
        $entry = Entry::factory()->create(['customer_id' => $customer->id]);

        $customerResponse = $this->actingAs($customer)->get(route('entries.show', $entry));
        $customerResponse->assertDontSee(route('entries.edit', $entry));

        $technicianResponse = $this->actingAs($technician)->get(route('entries.show', $entry));
        $technicianResponse->assertSee(route('entries.edit', $entry));
    }

    public function test_the_show_page_reports_when_an_entry_has_no_pictures(): void
    {
        $admin = User::factory()->admin()->create();
        $entry = Entry::factory()->create();

        $response = $this->actingAs($admin)->get(route('entries.show', $entry));

        $response->assertSee('The customer of this entry did not insert any pictures');
    }

    public function test_the_customer_column_is_hidden_from_customers_but_shown_to_staff(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->admin()->create();
        Entry::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer)->get(route('entries.index'))->assertDontSee('Customer');
        $this->actingAs($admin)->get(route('entries.index'))->assertSee('Customer');
    }

    public function test_update_changes_the_entrys_name_unit_problem_and_status(): void
    {
        $technician = User::factory()->technician()->create();
        $entry = Entry::factory()->create(['status' => '1']);

        $response = $this->actingAs($technician)->put(route('entries.update', $entry), [
            'name_unit' => 'New Unit Name',
            'problem' => 'Updated problem description',
            'status' => '3',
        ]);

        $response->assertRedirect(route('entries.index'));
        $entry->refresh();
        $this->assertSame('New Unit Name', $entry->name_unit);
        $this->assertSame('Updated problem description', $entry->problem);
        $this->assertSame('3', $entry->status);
    }

    public function test_update_requires_name_unit_problem_and_a_valid_status(): void
    {
        $technician = User::factory()->technician()->create();
        $entry = Entry::factory()->create();

        $response = $this->actingAs($technician)->put(route('entries.update', $entry), [
            'name_unit' => '',
            'problem' => '',
            'status' => '9', // not a real entry_status row
        ]);

        $response->assertSessionHasErrors(['name_unit', 'problem', 'status']);
    }

    public function test_destroy_deletes_the_entry_and_redirects_with_success_flash(): void
    {
        $admin = User::factory()->admin()->create();
        $entry = Entry::factory()->create();

        $response = $this->actingAs($admin)->delete(route('entries.destroy', $entry));

        $response->assertRedirect(route('entries.index'));
        $response->assertSessionHas('success');
        $this->assertModelMissing($entry);
    }
}
