<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Accession extends Model
{
    protected $fillable = ['code', 'name', 'plant_group_id'];

    /** N : 1 -> banyak aksesi milik satu kelompok tanaman */
    public function plantGroup(): BelongsTo
    {
        return $this->belongsTo(PlantGroup::class);
    }

    /** 1 : 1 -> satu aksesi punya satu detail asal pengumpulan */
    public function origin(): HasOne
    {
        return $this->hasOne(CollectionOrigin::class);
    }

    /** N : M -> satu aksesi dapat memperoleh beberapa perlakuan penyimpanan */
    public function treatments(): BelongsToMany
    {
        return $this->belongsToMany(StorageTreatment::class, 'accession_storage_treatment')
                    ->withTimestamps();
    }

    /* ---------------- Query scope: pencarian & filter ---------------- */

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, function (Builder $q) use ($keyword) {
            $q->where(function (Builder $w) use ($keyword) {
                $w->where('code', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%")
                  ->orWhereHas('origin', function (Builder $o) use ($keyword) {
                      $o->where('province', 'like', "%{$keyword}%")
                        ->orWhere('regency', 'like', "%{$keyword}%")
                        ->orWhere('village', 'like', "%{$keyword}%");
                  });
            });
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['plant_group_id'] ?? null,
                fn (Builder $q, $id) => $q->where('plant_group_id', $id))
            ->when($filters['treatment_id'] ?? null,
                fn (Builder $q, $id) => $q->whereHas('treatments',
                    fn (Builder $t) => $t->where('storage_treatments.id', $id)))
            ->when($filters['province'] ?? null,
                fn (Builder $q, $prov) => $q->whereHas('origin',
                    fn (Builder $o) => $o->where('province', $prov)));
    }
}
