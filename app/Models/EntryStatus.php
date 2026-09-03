<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['status_id', 'status'])]
class EntryStatus extends Model
{
    protected $table = 'entry_status';

    protected $primaryKey = 'status_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * Every entry created by a customer starts in this status.
     */
    public const PENDING = '1';

    public function entries()
    {
        return $this->hasMany(Entry::class, 'status', 'status_id');
    }
}
