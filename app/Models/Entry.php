<?php

namespace App\Models;

use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['entry_id', 'customer_id', 'name_unit', 'problem', 'entry_date', 'entry_time', 'status'])]
class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory;

    protected $table = 'entry';

    protected $primaryKey = 'entry_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'entry_time' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $entry) {
            if (! $entry->entry_id) {
                do {
                    $id = Str::upper(Str::random(8));
                } while (self::where('entry_id', $id)->exists());

                $entry->entry_id = $id;
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Named `entryStatus`, not `status` — `status` is already the raw
     * column name, and Eloquent always resolves a same-named attribute
     * over a relationship method, so `status()` would be unreachable via
     * `$entry->status`.
     */
    public function entryStatus()
    {
        return $this->belongsTo(EntryStatus::class, 'status', 'status_id');
    }

    public function pictures()
    {
        return $this->hasMany(EntryPicture::class, 'entry_id', 'entry_id');
    }
}
