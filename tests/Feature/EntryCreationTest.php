<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\EntryPicture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EntryCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_reach_the_create_page(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('entries.create'));

        $response->assertOk();
        $response->assertSee('Creating New Entry');
    }

    public function test_a_technician_and_an_admin_can_not_reach_the_create_page(): void
    {
        $technician = User::factory()->technician()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($technician)->get(route('entries.create'))->assertForbidden();
        $this->actingAs($admin)->get(route('entries.create'))->assertForbidden();
    }

    public function test_a_guest_is_redirected_to_login_from_the_create_page(): void
    {
        $response = $this->get(route('entries.create'));

        $response->assertRedirect('/login');
    }

    public function test_store_creates_an_entry_owned_by_the_authenticated_customer(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('entries.store'), [
            'name_unit' => 'ROG Flow Z13',
            'problem' => "Doesn't display anything when I boot it up.",
        ]);

        $response->assertRedirect(route('entries.index'));
        $entry = Entry::first();
        $this->assertNotNull($entry);
        $this->assertSame($customer->id, $entry->customer_id);
        $this->assertSame('1', $entry->status);
    }

    public function test_a_technician_and_an_admin_can_not_store_an_entry(): void
    {
        $technician = User::factory()->technician()->create();
        $admin = User::factory()->admin()->create();

        $payload = ['name_unit' => 'ROG Flow Z13', 'problem' => 'Broken.'];

        $this->actingAs($technician)->post(route('entries.store'), $payload)->assertForbidden();
        $this->actingAs($admin)->post(route('entries.store'), $payload)->assertForbidden();
        $this->assertSame(0, Entry::count());
    }

    public function test_store_requires_name_unit_and_problem(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('entries.store'), []);

        $response->assertSessionHasErrors(['name_unit', 'problem']);
        $this->assertSame(0, Entry::count());
    }

    public function test_store_uploads_pictures_into_the_entrys_own_directory(): void
    {
        Storage::fake('public');
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('entries.store'), [
            'name_unit' => 'ROG Flow Z13',
            'problem' => 'Broken screen.',
            'images' => [
                UploadedFile::fake()->image('front.jpg')->size(100),
                UploadedFile::fake()->image('back.jpg')->size(100),
            ],
        ]);

        $response->assertRedirect(route('entries.index'));
        $entry = Entry::firstOrFail();
        $this->assertSame(2, $entry->pictures()->count());

        foreach ($entry->pictures as $picture) {
            Storage::disk('public')->assertExists("entry_pictures/{$entry->entry_id}/{$picture->file_name}");
        }
    }

    public function test_store_rejects_more_than_five_pictures(): void
    {
        Storage::fake('public');
        $customer = User::factory()->create(['role' => 'customer']);

        $images = collect(range(1, 6))
            ->map(fn ($i) => UploadedFile::fake()->image("photo{$i}.jpg")->size(100))
            ->all();

        $response = $this->actingAs($customer)->post(route('entries.store'), [
            'name_unit' => 'ROG Flow Z13',
            'problem' => 'Broken screen.',
            'images' => $images,
        ]);

        $response->assertSessionHasErrors('images');
        $this->assertSame(0, Entry::count());
    }

    public function test_store_rejects_pictures_totalling_more_than_10mb(): void
    {
        Storage::fake('public');
        $customer = User::factory()->create(['role' => 'customer']);

        $images = [
            UploadedFile::fake()->image('big1.jpg')->size(6 * 1024),
            UploadedFile::fake()->image('big2.jpg')->size(6 * 1024),
        ];

        $response = $this->actingAs($customer)->post(route('entries.store'), [
            'name_unit' => 'ROG Flow Z13',
            'problem' => 'Broken screen.',
            'images' => $images,
        ]);

        $response->assertSessionHasErrors('images');
        $this->assertSame(0, Entry::count());
    }

    public function test_destroy_removes_the_entrys_picture_directory(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $entry = Entry::factory()->create();
        Storage::disk('public')->put("entry_pictures/{$entry->entry_id}/photo.jpg", 'fake-contents');
        EntryPicture::factory()->create(['entry_id' => $entry->entry_id, 'file_name' => 'photo.jpg']);

        $this->actingAs($admin)->delete(route('entries.destroy', $entry))->assertRedirect(route('entries.index'));

        Storage::disk('public')->assertMissing("entry_pictures/{$entry->entry_id}/photo.jpg");
        $this->assertSame(0, EntryPicture::count());
    }

    public function test_updating_with_remove_pictures_deletes_only_the_listed_pictures(): void
    {
        Storage::fake('public');
        $technician = User::factory()->technician()->create();
        $entry = Entry::factory()->create();
        Storage::disk('public')->put("entry_pictures/{$entry->entry_id}/keep.jpg", 'fake-contents');
        Storage::disk('public')->put("entry_pictures/{$entry->entry_id}/drop.jpg", 'fake-contents');
        $keep = EntryPicture::factory()->create(['entry_id' => $entry->entry_id, 'file_name' => 'keep.jpg']);
        $drop = EntryPicture::factory()->create(['entry_id' => $entry->entry_id, 'file_name' => 'drop.jpg']);

        $response = $this->actingAs($technician)->put(route('entries.update', $entry), [
            'name_unit' => $entry->name_unit,
            'problem' => $entry->problem,
            'status' => $entry->status,
            'remove_pictures' => [$drop->id],
        ]);

        $response->assertRedirect(route('entries.index'));
        $this->assertModelMissing($drop);
        $this->assertModelExists($keep);
        Storage::disk('public')->assertMissing("entry_pictures/{$entry->entry_id}/drop.jpg");
        Storage::disk('public')->assertExists("entry_pictures/{$entry->entry_id}/keep.jpg");
    }

    public function test_a_pictures_id_from_another_entry_can_not_be_removed_through_this_entry(): void
    {
        Storage::fake('public');
        $technician = User::factory()->technician()->create();
        $entry = Entry::factory()->create();
        $otherEntry = Entry::factory()->create();
        Storage::disk('public')->put("entry_pictures/{$otherEntry->entry_id}/photo.jpg", 'fake-contents');
        $foreignPicture = EntryPicture::factory()->create(['entry_id' => $otherEntry->entry_id, 'file_name' => 'photo.jpg']);

        $this->actingAs($technician)->put(route('entries.update', $entry), [
            'name_unit' => $entry->name_unit,
            'problem' => $entry->problem,
            'status' => $entry->status,
            'remove_pictures' => [$foreignPicture->id],
        ]);

        $this->assertModelExists($foreignPicture);
        Storage::disk('public')->assertExists("entry_pictures/{$otherEntry->entry_id}/photo.jpg");
    }

    public function test_update_rejects_new_pictures_that_would_exceed_the_cap_after_accounting_for_removals(): void
    {
        Storage::fake('public');
        $technician = User::factory()->technician()->create();
        $entry = Entry::factory()->create();

        // 5 existing pictures already at the cap — without removing any of
        // them, even one new picture should be rejected.
        for ($i = 0; $i < 5; $i++) {
            Storage::disk('public')->put("entry_pictures/{$entry->entry_id}/photo{$i}.jpg", 'fake-contents');
            EntryPicture::factory()->create(['entry_id' => $entry->entry_id, 'file_name' => "photo{$i}.jpg"]);
        }

        $response = $this->actingAs($technician)->put(route('entries.update', $entry), [
            'name_unit' => $entry->name_unit,
            'problem' => $entry->problem,
            'status' => $entry->status,
            'images' => [UploadedFile::fake()->image('new.jpg')->size(100)],
        ]);

        $response->assertSessionHasErrors('images');
        $this->assertSame(5, $entry->pictures()->count());
    }
}
