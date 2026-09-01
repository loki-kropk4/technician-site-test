<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled at the route level (the `can:admin`
        // middleware on the admin.users resource route in routes/web.php,
        // plus the global `auth` middleware), which already requires an
        // authenticated admin before this request class is constructed.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            // Old Password is only required when a New Password is being set —
            // updating name/email alone shouldn't force re-entering the password.
            'old_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('new_password') || ! $this->filled('old_password')) {
                // No password change requested, or required_with already flagged the miss.
                return;
            }

            if (! Hash::check($this->input('old_password'), $this->route('user')->password)) {
                $validator->errors()->add('old_password', 'The old password is incorrect.');
            }
        });
    }
}
