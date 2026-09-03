<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntryAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_the_entries_index(): void
    {
        $response = $this->get(route('entries.index'));

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_editing_an_entry(): void
    {
        $entry = Entry::factory()->create();

        $response = $this->get(route('entries.edit', $entry));

        $response->assertRedirect('/login');
    }

    public function test_a_customer_can_not_edit_update_or_delete_even_their_own_entry(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $entry = Entry::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer);

        $this->get(route('entries.edit', $entry))->assertForbidden();
        $this->put(route('entries.update', $entry), [
            'name_unit' => 'Renamed Unit',
            'problem' => 'Still broken',
            'status' => '2',
        ])->assertForbidden();
        $this->delete(route('entries.destroy', $entry))->assertForbidden();

        $this->assertModelExists($entry);
    }

    public function test_a_technician_can_edit_update_and_delete_any_entry(): void
    {
        $technician = User::factory()->technician()->create();
        $entry = Entry::factory()->create();

        $this->actingAs($technician);

        $this->get(route('entries.edit', $entry))->assertOk();
        $this->put(route('entries.update', $entry), [
            'name_unit' => 'Renamed Unit',
            'problem' => 'Still broken',
            'status' => '2',
        ])->assertRedirect(route('entries.index'));
        $this->delete(route('entries.destroy', $entry))->assertRedirect(route('entries.index'));

        $this->assertModelMissing($entry);
    }

    public function test_the_admin_can_edit_update_and_delete_any_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $entry = Entry::factory()->create();

        $this->actingAs($admin);

        $this->get(route('entries.edit', $entry))->assertOk();
        $this->put(route('entries.update', $entry), [
            'name_unit' => 'Renamed Unit',
            'problem' => 'Still broken',
            'status' => '2',
        ])->assertRedirect(route('entries.index'));
        $this->delete(route('entries.destroy', $entry))->assertRedirect(route('entries.index'));

        $this->assertModelMissing($entry);
    }
}
