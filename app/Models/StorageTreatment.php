<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StorageTreatment extends Model
{
    protected $fillable = ['name', 'description'];

    public function accessions(): BelongsToMany
    {
        return $this->belongsToMany(Accession::class, 'accession_storage_treatment')->withTimestamps();
    }
}
