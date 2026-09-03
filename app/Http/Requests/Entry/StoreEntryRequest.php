<?php

namespace App\Http\Requests\Entry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEntryRequest extends FormRequest
{
    /**
     * An entry may have at most this many pictures, totalling at most
     * this many bytes — enforced in withValidator() below, since neither
     * limit is expressible as a plain per-field rule.
     */
    private const MAX_PICTURES = 5;

    private const MAX_TOTAL_BYTES = 10 * 1024 * 1024;

    public function authorize(): bool
    {
        // Authorization is handled at the route level (the `can:create-entry`
        // middleware on the entries.store route in routes/web.php, plus
        // the global `auth` middleware), which already requires an
        // authenticated customer before this request class is constructed.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name_unit' => ['required', 'string', 'max:40'],
            'problem' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $images = $this->file('images', []);

            if (count($images) > self::MAX_PICTURES) {
                $validator->errors()->add('images', 'An entry may have at most '.self::MAX_PICTURES.' pictures.');
            }

            $totalBytes = collect($images)->sum(fn ($file) => $file->getSize());

            if ($totalBytes > self::MAX_TOTAL_BYTES) {
                $validator->errors()->add('images', 'Pictures may not exceed 10 MB in total.');
            }
        });
    }
}
