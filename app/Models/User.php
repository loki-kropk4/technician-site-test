<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Rules\UniqueAdminRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rule;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Validation rules for the `role` column: restricts it to the
     * UserRole values, and guarantees "admin" is held by at most one user.
     *
     * @param  int|null  $ignoreUserId  Pass the user's own id when updating,
     *                                  so it doesn't get flagged against itself.
     * @return array<int, mixed>
     */
    public static function roleRules(?int $ignoreUserId = null): array
    {
        return [
            'required',
            Rule::enum(UserRole::class),
            new UniqueAdminRole($ignoreUserId),
        ];
    }
}
