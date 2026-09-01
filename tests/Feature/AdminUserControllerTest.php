<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // The admin.users.* routes now require an authenticated admin (see
        // the `can:admin` middleware in routes/web.php) — act as the sole
        // admin user for every test in this class.
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_index_displays_all_users(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Jane Doe');
        $response->assertSee('jane@example.com');
    }

    public function test_index_greets_the_admin_by_name_when_one_exists(): void
    {
        // setUp() already created the sole admin (only one may exist —
        // see UniqueAdminRole/admin_slot), so it's who the page must greet.
        $response = $this->get(route('admin.users.index'));

        $response->assertSee("Welcome back, {$this->admin->name}");
    }

    public function test_index_sorts_users_by_a_whitelisted_column_and_toggles_direction(): void
    {
        User::factory()->create(['name' => 'Zed']);
        User::factory()->create(['name' => 'Amy']);

        $asc = $this->get(route('admin.users.index', ['sort' => 'name', 'direction' => 'asc']));
        $names = $asc->viewData('users')->pluck('name')->all();
        $this->assertSame(['Amy', $this->admin->name, 'Zed'], $names);

        $desc = $this->get(route('admin.users.index', ['sort' => 'name', 'direction' => 'desc']));
        $names = $desc->viewData('users')->pluck('name')->all();
        $this->assertSame(['Zed', $this->admin->name, 'Amy'], $names);
    }

    public function test_index_falls_back_to_default_sort_for_a_non_whitelisted_column(): void
    {
        User::factory()->create();

        $response = $this->get(route('admin.users.index', ['sort' => 'password']));

        $response->assertStatus(200);
        $this->assertSame('id', $response->viewData('sort'));
    }

    public function test_create_page_is_accessible(): void
    {
        $response = $this->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('Making New User Record');
    }

    public function test_store_creates_a_technician_and_ignores_any_role_input(): void
    {
        $response = $this->post(route('admin.users.store'), [
            'name' => 'Tom Tech',
            'email' => 'tom@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'tom@example.com')->firstOrFail();
        $this->assertSame(UserRole::Technician, $user->role);
    }

    public function test_store_requires_name_email_and_password(): void
    {
        $response = $this->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertSame(1, User::count()); // just the acting admin
    }

    public function test_store_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertSame(2, User::count()); // acting admin + the pre-existing user
    }

    public function test_edit_page_prefills_existing_user_data(): void
    {
        $user = User::factory()->create(['name' => 'Existing User', 'email' => 'existing@example.com']);

        $response = $this->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertSee('Editing Existing User Record');
        $response->assertSee('existing@example.com');
    }

    public function test_update_changes_name_and_email_without_touching_password(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
        $originalHash = $user->password;

        $response = $this->put(route('admin.users.update', $user), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame($originalHash, $user->password);
    }

    public function test_update_changes_password_when_old_password_is_correct(): void
    {
        $user = User::factory()->create(['password' => Hash::make('current-secret')]);

        $response = $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'old_password' => 'current-secret',
            'new_password' => 'brand-new-secret',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('brand-new-secret', $user->refresh()->password));
    }

    public function test_update_fails_when_old_password_is_incorrect(): void
    {
        $user = User::factory()->create(['password' => Hash::make('current-secret')]);
        $originalHash = $user->password;

        $response = $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'old_password' => 'wrong-password',
            'new_password' => 'brand-new-secret',
        ]);

        $response->assertSessionHasErrors(['old_password']);
        $this->assertSame($originalHash, $user->refresh()->password);
    }

    public function test_update_fails_when_new_password_given_without_old_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('current-secret')]);
        $originalHash = $user->password;

        $response = $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'new_password' => 'brand-new-secret',
        ]);

        $response->assertSessionHasErrors(['old_password']);
        $this->assertSame($originalHash, $user->refresh()->password);
    }

    public function test_update_rejects_a_duplicate_email_but_ignores_the_users_own_row(): void
    {
        $user = User::factory()->create(['email' => 'mine@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        $sameEmail = $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => 'mine@example.com',
        ]);
        $sameEmail->assertSessionHasNoErrors();

        $takenEmail = $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => 'other@example.com',
        ]);
        $takenEmail->assertSessionHasErrors(['email']);
    }

    public function test_validation_failure_redirects_back_to_the_form_not_the_index(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('admin.users.edit', $user))
            ->put(route('admin.users.update', $user), ['name' => '', 'email' => '']);

        $response->assertRedirect(route('admin.users.edit', $user));
    }

    public function test_destroy_deletes_the_user_and_redirects_with_success_flash(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertModelMissing($user);
    }
}
