<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionOrigin extends Model
{
    protected $fillable = [
        'accession_id', 'province', 'regency', 'village',
        'altitude', 'collected_at', 'collector', 'notes',
    ];

    protected $casts = [
        'collected_at' => 'date',
    ];

    public function accession(): BelongsTo
    {
        return $this->belongsTo(Accession::class);
    }
}
