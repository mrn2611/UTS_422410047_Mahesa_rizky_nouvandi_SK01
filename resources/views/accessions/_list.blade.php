@php
    $sort = $filters['sort'] ?? 'id';
    $dir  = $filters['dir'] ?? 'desc';
    $sortUrl = fn ($col) => request()->fullUrlWithQuery([
        'sort' => $col,
        'dir'  => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc',
        'page' => null,
    ]);
    $caret = fn ($col) => $sort === $col ? '<i class="bi bi-caret-'.($dir === 'asc' ? 'up' : 'down').'-fill ms-1 small"></i>' : '';

    $chips = [];
    if (!empty($filters['q'])) { $chips[] = ['q', 'Kata kunci: '.$filters['q']]; }
    if (!empty($filters['plant_group_id'])) { $chips[] = ['plant_group_id', $plantGroups->firstWhere('id', $filters['plant_group_id'])?->name ?? 'Kelompok']; }
    if (!empty($filters['treatment_id'])) { $chips[] = ['treatment_id', $treatments->firstWhere('id', $filters['treatment_id'])?->name ?? 'Perlakuan']; }
    if (!empty($filters['province'])) { $chips[] = ['province', $filters['province']]; }
@endphp

@if ($chips)
    <div class="chips-row mb-3">
        <span class="text-muted small">Filter aktif:</span>
        @foreach ($chips as [$field, $label])
            <button type="button" class="chip-filter" data-clear="{{ $field }}">{{ $label }} <i class="bi bi-x-lg"></i></button>
        @endforeach
        <button type="button" class="btn btn-link btn-sm p-0" data-reset>Hapus semua</button>
    </div>
@endif

<div class="text-muted small mb-2">
    @if ($accessions->total())
        Menampilkan {{ $accessions->firstItem() }}–{{ $accessions->lastItem() }} dari {{ $accessions->total() }} aksesi
    @endif
</div>

@if ($accessions->count())
    {{-- Tampilan tabel --}}
    <div id="view-table" class="panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th><a href="{{ $sortUrl('code') }}" data-sort>Kode{!! $caret('code') !!}</a></th>
                    <th><a href="{{ $sortUrl('name') }}" data-sort>Nama aksesi{!! $caret('name') !!}</a></th>
                    <th>Kelompok</th>
                    <th>Asal</th>
                    <th>Perlakuan</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($accessions as $a)
                    <tr>
                        <td><span class="spec-tag">{{ $a->code }}</span></td>
                        <td><a href="{{ route('accessions.show', $a) }}" class="name-link">{{ $a->name }}</a></td>
                        <td class="text-nowrap"><span class="dot g-{{ $a->plant_group_id % 6 }} me-1"></span>{{ $a->plantGroup->name }}</td>
                        <td>{{ $a->origin?->regency }}<span class="text-muted">, {{ $a->origin?->province }}</span></td>
                        <td>
                            @foreach ($a->treatments as $t)
                                <span class="chip">{{ $t->name }}</span>
                            @endforeach
                        </td>
                        <td class="text-end text-nowrap row-actions">
                            <a href="{{ route('accessions.show', $a) }}" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('accessions.edit', $a) }}" class="btn btn-sm btn-outline-primary" title="Ubah"><i class="bi bi-pencil"></i></a>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                    data-delete data-action="{{ route('accessions.destroy', $a) }}"
                                    data-name="{{ $a->name }} ({{ $a->code }})"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tampilan kartu --}}
    <div id="view-grid" class="row g-3 d-none">
        @foreach ($accessions as $a)
            <div class="col-md-6 col-xl-4">
                <article class="panel acc-card g-{{ $a->plant_group_id % 6 }}">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <span class="spec-tag">{{ $a->code }}</span>
                        <span class="small text-muted text-end"><span class="dot me-1"></span>{{ $a->plantGroup->name }}</span>
                    </div>
                    <h3><a href="{{ route('accessions.show', $a) }}">{{ $a->name }}</a></h3>
                    <div class="meta"><i class="bi bi-geo-alt me-1"></i>{{ $a->origin?->regency }}, {{ $a->origin?->province }}</div>
                    <div>
                        @forelse ($a->treatments as $t)
                            <span class="chip">{{ $t->name }}</span>
                        @empty
                            <span class="text-muted small">Belum ada perlakuan</span>
                        @endforelse
                    </div>
                    <div class="mt-auto d-flex gap-2 pt-1 row-actions">
                        <a href="{{ route('accessions.show', $a) }}" class="btn btn-sm btn-outline-secondary flex-fill">Lihat</a>
                        <a href="{{ route('accessions.edit', $a) }}" class="btn btn-sm btn-outline-primary flex-fill">Ubah</a>
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                data-delete data-action="{{ route('accessions.destroy', $a) }}"
                                data-name="{{ $a->name }} ({{ $a->code }})"><i class="bi bi-trash"></i></button>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
@else
    <div class="panel empty">
        <i class="bi bi-search"></i>
        @if ($chips)
            <p class="mt-2 mb-3">Tidak ada aksesi yang cocok dengan pencarian atau filter ini.</p>
            <button type="button" class="btn btn-outline-primary" data-reset>Hapus semua filter</button>
        @else
            <p class="mt-2 mb-3">Belum ada aksesi. Tambahkan benih pertama ke koleksi.</p>
            <a href="{{ route('accessions.create') }}" class="btn btn-primary">Tambah aksesi</a>
        @endif
    </div>
@endif

<div class="d-flex justify-content-end mt-3 page-wrap">
    {{ $accessions->onEachSide(1)->links() }}
</div>
