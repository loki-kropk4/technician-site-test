<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Entry\StoreEntryRequest;
use App\Http\Requests\Entry\UpdateEntryRequest;
use App\Models\Entry;
use App\Models\EntryPicture;
use App\Models\EntryStatus;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        $query = Entry::query()
            ->with(['customer', 'entryStatus'])
            ->orderByDesc('entry_date')
            ->orderByDesc('entry_time');

        if ($request->user()->role === UserRole::Customer) {
            $query->where('customer_id', $request->user()->id);
        }

        $entries = $query->paginate(15)->withQueryString();

        return view('entries.index', ['entries' => $entries]);
    }

    public function create()
    {
        return view('entries.form', ['entry' => null, 'statuses' => null]);
    }

    public function store(StoreEntryRequest $request)
    {
        $entry = Entry::create([
            'customer_id' => $request->user()->id,
            'name_unit' => $request->validated('name_unit'),
            'problem' => $request->validated('problem'),
            'entry_date' => now()->toDateString(),
            'entry_time' => now()->toTimeString(),
            'status' => EntryStatus::PENDING,
        ]);

        $this->storeUploadedPictures($entry, $request->file('images', []));

        return redirect()
            ->route('entries.index')
            ->with('success', 'Entry submitted.');
    }

    public function edit(Entry $entry)
    {
        return view('entries.form', [
            'entry' => $entry->load('pictures'),
            'statuses' => EntryStatus::all(),
        ]);
    }

    public function update(UpdateEntryRequest $request, Entry $entry)
    {
        $entry->update($request->safe()->only(['name_unit', 'problem', 'status']));

        // Pictures a technician removed in the form are only staged
        // client-side (see the dropzone script) — nothing is actually
        // deleted until this update saves, so accidentally clicking remove
        // doesn't lose a picture before "Save Changes" is pressed.
        $this->removeStagedPictures($entry, $request->input('remove_pictures', []));
        $this->storeUploadedPictures($entry, $request->file('images', []));

        return redirect()
            ->route('entries.index')
            ->with('success', 'Entry updated.');
    }

    public function destroy(Entry $entry)
    {
        Storage::disk('public')->deleteDirectory("entry_pictures/{$entry->entry_id}");

        $entry->delete();

        return redirect()
            ->route('entries.index')
            ->with('success', 'Entry deleted.');
    }

    /**
     * @param  array<int, int|string>  $pictureIds
     */
    private function removeStagedPictures(Entry $entry, array $pictureIds): void
    {
        if (empty($pictureIds)) {
            return;
        }

        // Scoped through the entry's own pictures relation, so a picture
        // id belonging to a different entry can never be deleted here.
        $entry->pictures()->whereIn('id', $pictureIds)->get()->each(function (EntryPicture $picture) use ($entry) {
            Storage::disk('public')->delete("entry_pictures/{$entry->entry_id}/{$picture->file_name}");
            $picture->delete();
        });
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    private function storeUploadedPictures(Entry $entry, array $images): void
    {
        if (empty($images)) {
            return;
        }

        // The [ENTRY_ID] directory is created explicitly before any file is
        // stored in it, per spec — storeAs() below would create it
        // implicitly anyway, but this makes the step explicit.
        Storage::disk('public')->makeDirectory("entry_pictures/{$entry->entry_id}");

        foreach ($images as $image) {
            $filename = $image->hashName();
            $image->storeAs("entry_pictures/{$entry->entry_id}", $filename, 'public');

            EntryPicture::create([
                'entry_id' => $entry->entry_id,
                'file_name' => $filename,
            ]);
        }
    }
}
