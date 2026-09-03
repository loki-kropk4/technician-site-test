<?php

namespace App\Http\Requests\Entry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEntryRequest extends FormRequest
{
    /**
     * An entry may have at most this many pictures, totalling at most
     * this many bytes — existing pictures count toward both limits, so
     * this is enforced in withValidator() below rather than a plain rule.
     */
    private const MAX_PICTURES = 5;

    private const MAX_TOTAL_BYTES = 10 * 1024 * 1024;

    public function authorize(): bool
    {
        // Authorization is handled at the route level (the `can:staff`
        // middleware on the entries.update route in routes/web.php, plus
        // the global `auth` middleware), which already requires an
        // authenticated technician/admin before this request class is
        // constructed.
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
            'status' => ['required', Rule::exists('entry_status', 'status_id')],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:10240'],
            // Existing pictures the technician staged for removal (see the
            // dropzone's remove button) — not deleted until this update
            // actually saves.
            'remove_pictures' => ['nullable', 'array'],
            'remove_pictures.*' => ['integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $entry = $this->route('entry');
            $newImages = $this->file('images', []);
            $removedIds = collect($this->input('remove_pictures', []))->map(fn ($id) => (int) $id);

            $remainingExisting = $entry->pictures->reject(fn ($picture) => $removedIds->contains($picture->id));

            if ($remainingExisting->count() + count($newImages) > self::MAX_PICTURES) {
                $validator->errors()->add('images', 'An entry may have at most '.self::MAX_PICTURES.' pictures.');
            }

            $existingBytes = $remainingExisting->sum(
                fn ($picture) => Storage::disk('public')->size("entry_pictures/{$entry->entry_id}/{$picture->file_name}")
            );
            $newBytes = collect($newImages)->sum(fn ($file) => $file->getSize());

            if ($existingBytes + $newBytes > self::MAX_TOTAL_BYTES) {
                $validator->errors()->add('images', 'Pictures may not exceed 10 MB in total.');
            }
        });
    }
}
