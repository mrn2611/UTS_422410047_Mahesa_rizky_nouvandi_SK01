<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantGroup extends Model
{
    protected $fillable = ['name', 'description'];

    public function accessions(): HasMany
    {
        return $this->hasMany(Accession::class);
    }
}
