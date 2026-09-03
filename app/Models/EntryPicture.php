<?php

namespace App\Models;

use Database\Factories\EntryPictureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['entry_id', 'file_name'])]
class EntryPicture extends Model
{
    /** @use HasFactory<EntryPictureFactory> */
    use HasFactory;

    protected $table = 'entry_picture';

    public function entry()
    {
        return $this->belongsTo(Entry::class, 'entry_id', 'entry_id');
    }

    /**
     * Public URL for this picture, served through the `public/storage`
     * symlink (see `storage/app/public/entry_pictures/{entry_id}/`).
     */
    public function url(): string
    {
        return Storage::disk('public')->url("entry_pictures/{$this->entry_id}/{$this->file_name}");
    }

    /**
     * File size in bytes, read from disk (not stored on the row).
     */
    public function size(): int
    {
        return Storage::disk('public')->size("entry_pictures/{$this->entry_id}/{$this->file_name}");
    }
}
