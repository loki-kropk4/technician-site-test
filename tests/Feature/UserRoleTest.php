<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_accepts_the_allowed_values(): void
    {
        foreach (UserRole::cases() as $role) {
            $validator = validator(['role' => $role->value], ['role' => User::roleRules()]);

            $this->assertFalse($validator->fails(), "Expected role '{$role->value}' to be valid.");

            User::query()->delete();
        }
    }

    public function test_role_rejects_a_value_outside_the_allowed_list(): void
    {
        $validator = validator(['role' => 'manager'], ['role' => User::roleRules()]);

        $this->assertTrue($validator->fails());
    }

    public function test_only_one_user_can_hold_the_admin_role(): void
    {
        User::factory()->create(['role' => UserRole::Admin]);

        $validator = validator(['role' => 'admin'], ['role' => User::roleRules()]);

        $this->assertTrue($validator->fails());
    }

    public function test_admin_uniqueness_validation_ignores_the_current_user_when_updating(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $validator = validator(['role' => 'admin'], ['role' => User::roleRules($admin->id)]);

        $this->assertFalse($validator->fails());
    }

    public function test_the_database_rejects_a_second_admin_row_even_without_validation(): void
    {
        User::factory()->create(['role' => UserRole::Admin]);

        $this->expectException(QueryException::class);

        User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_role_is_cast_to_the_user_role_enum(): void
    {
        $user = User::factory()->create(['role' => UserRole::Technician]);

        $this->assertSame(UserRole::Technician, $user->refresh()->role);
    }
}
