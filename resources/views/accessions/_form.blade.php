@php
    $o = $accession->origin;
    $selectedTreatments = old('treatments', $accession->exists ? $accession->treatments->pluck('id')->all() : []);
@endphp

@if ($errors->any())
    <div class="alert alert-danger d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div><strong>Data belum bisa disimpan.</strong> Periksa isian yang ditandai merah di bawah.</div>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">

        {{-- 1. Identitas --}}
        <section class="panel p-4 mb-3">
            <div class="panel-head"><span class="step">1</span><h2 class="panel-title">Identitas aksesi</h2></div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="code" class="form-label">Kode aksesi</label>
                    <div class="input-group has-validation">
                        <input type="text" id="code" name="code" value="{{ old('code', $accession->code) }}" required maxlength="30"
                               class="form-control @error('code') is-invalid @enderror" placeholder="BBP-0009" autocomplete="off">
                        @if (!empty($nextCode))
                            <button type="button" class="btn btn-outline-secondary" id="btn-next-code" data-code="{{ $nextCode }}" title="Isi dengan kode berikutnya">Otomatis</button>
                        @endif
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div id="code-hint" class="form-text"></div>
                </div>
                <div class="col-md-7">
                    <label for="name" class="form-label">Nama aksesi</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $accession->name) }}" required maxlength="150"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Padi Pandanwangi">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="plant_group_id" class="form-label">Kelompok tanaman</label>
                    <select id="plant_group_id" name="plant_group_id" required class="form-select @error('plant_group_id') is-invalid @enderror">
                        <option value="">Pilih kelompok</option>
                        @foreach ($plantGroups as $g)
                            <option value="{{ $g->id }}" @selected(old('plant_group_id', $accession->plant_group_id) == $g->id)>{{ $g->name }}</option>
                        @endforeach
                    </select>
                    @error('plant_group_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        {{-- 2. Asal --}}
        <section class="panel p-4 mb-3">
            <div class="panel-head"><span class="step">2</span><h2 class="panel-title">Asal pengumpulan</h2></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="province" class="form-label">Provinsi</label>
                    <input type="text" id="province" name="origin[province]" value="{{ old('origin.province', $o?->province) }}" required maxlength="100"
                           class="form-control @error('origin.province') is-invalid @enderror">
                    @error('origin.province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="regency" class="form-label">Kabupaten/Kota</label>
                    <input type="text" id="regency" name="origin[regency]" value="{{ old('origin.regency', $o?->regency) }}" required maxlength="100"
                           class="form-control @error('origin.regency') is-invalid @enderror">
                    @error('origin.regency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="village" class="form-label">Desa/Kecamatan <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="text" id="village" name="origin[village]" value="{{ old('origin.village', $o?->village) }}" maxlength="100"
                           class="form-control @error('origin.village') is-invalid @enderror">
                    @error('origin.village') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="altitude" class="form-label">Ketinggian (mdpl) <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="number" id="altitude" name="origin[altitude]" value="{{ old('origin.altitude', $o?->altitude) }}" min="0" max="5000"
                           class="form-control @error('origin.altitude') is-invalid @enderror">
                    @error('origin.altitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="collected_at" class="form-label">Tanggal pengumpulan</label>
                    <input type="date" id="collected_at" name="origin[collected_at]" required max="{{ now()->toDateString() }}"
                           value="{{ old('origin.collected_at', $o?->collected_at?->format('Y-m-d')) }}"
                           class="form-control @error('origin.collected_at') is-invalid @enderror">
                    @error('origin.collected_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="collector" class="form-label">Nama pengumpul <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="text" id="collector" name="origin[collector]" value="{{ old('origin.collector', $o?->collector) }}" maxlength="100"
                           class="form-control @error('origin.collector') is-invalid @enderror">
                    @error('origin.collector') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                    <textarea id="notes" name="origin[notes]" rows="3" maxlength="1000"
                              class="form-control @error('origin.notes') is-invalid @enderror">{{ old('origin.notes', $o?->notes) }}</textarea>
                    <div class="counter" id="notes-count">0 / 1000</div>
                    @error('origin.notes') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        {{-- 3. Perlakuan --}}
        <section class="panel p-4 mb-3">
            <div class="panel-head">
                <span class="step">3</span>
                <h2 class="panel-title">Perlakuan penyimpanan</h2>
                <span class="ms-auto small text-muted"><span id="treat-count">0</span> dipilih</span>
            </div>
            <p class="text-muted">Ketuk kartu untuk memilih. Satu aksesi boleh memiliki beberapa perlakuan.</p>
            <div class="row g-2">
                @foreach ($treatments as $t)
                    <div class="col-sm-6">
                        <label class="treat-card">
                            <input type="checkbox" name="treatments[]" value="{{ $t->id }}" data-label="{{ $t->name }}"
                                   @checked(in_array($t->id, $selectedTreatments))>
                            <span class="treat-body">
                                <i class="bi bi-check-circle-fill tick"></i>
                                <strong>{{ $t->name }}</strong>
                                <small>{{ $t->description }}</small>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 small">
                <button type="button" class="btn btn-link btn-sm p-0" id="treat-all">Pilih semua</button>
                <span class="text-muted mx-1">/</span>
                <button type="button" class="btn btn-link btn-sm p-0" id="treat-none">Kosongkan</button>
            </div>
            @error('treatments') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            @error('treatments.*') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </section>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary px-4" id="btn-save"><i class="bi bi-check2 me-1"></i>Simpan aksesi</button>
            <a href="{{ route('accessions.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>

    {{-- Pratinjau label aksesi: berubah langsung saat mengetik --}}
    <div class="col-lg-4">
        <aside class="panel p-4 preview" aria-label="Pratinjau aksesi">
            <div class="tagline mb-2">Pratinjau label aksesi</div>
            <span class="spec-tag lg mb-2" data-out="code" data-empty="BBP-····">BBP-····</span>
            <h2 class="h5 mb-0 mt-2" data-out="name" data-empty="Nama belum diisi">Nama belum diisi</h2>
            <div class="text-muted" data-out="group" data-empty="Kelompok belum dipilih">Kelompok belum dipilih</div>
            <dl>
                <dt>Asal</dt><dd data-out="place" data-empty="-">-</dd>
                <dt>Ketinggian</dt><dd data-out="altitude" data-empty="-">-</dd>
                <dt>Tanggal pengumpulan</dt><dd data-out="date" data-empty="-">-</dd>
                <dt>Pengumpul</dt><dd data-out="collector" data-empty="-">-</dd>
                <dt>Perlakuan</dt><dd data-out-treat></dd>
            </dl>
            <hr>
            <div class="progress" role="progressbar" aria-label="Kelengkapan isian wajib"><div class="progress-bar" id="progress-bar" style="width:0%"></div></div>
            <div class="small text-muted mt-1" id="progress-text"></div>
        </aside>
    </div>
</div>
