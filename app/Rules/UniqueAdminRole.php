<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Ensures the "admin" role is only ever held by a single user.
 *
 * Other role values (customer, technician) are left untouched — pass this
 * alongside a Rule::enum(UserRole::class) check for the allowed-values rule.
 */
class UniqueAdminRole implements ValidationRule
{
    /**
     * @param  int|null  $ignoreUserId  Exclude this user id from the uniqueness
     *                                  check (e.g. the user's own id when updating).
     */
    public function __construct(private readonly ?int $ignoreUserId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $role = $value instanceof UserRole ? $value->value : $value;

        if ($role !== UserRole::Admin->value) {
            return;
        }

        $alreadyTaken = User::query()
            ->where('role', UserRole::Admin->value)
            ->when($this->ignoreUserId, fn ($query) => $query->where('id', '!=', $this->ignoreUserId))
            ->exists();

        if ($alreadyTaken) {
            $fail('The admin role is already assigned to another user.');
        }
    }
}
