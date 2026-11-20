(() => {
    'use strict';
    const $ = (s, c = document) => c.querySelector(s);
    const $$ = (s, c = document) => [...c.querySelectorAll(s)];
    const store = {
        get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* abaikan */ } },
    };

    /* ---------- Toast ---------- */
    function toast(msg, type = 'success') {
        const wrap = $('#toast-wrap');
        if (!wrap) return;
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type} border-0`;
        el.setAttribute('role', 'status');
        el.innerHTML = '<div class="d-flex"><div class="toast-body"></div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button></div>';
        el.querySelector('.toast-body').textContent = msg;
        wrap.appendChild(el);
        new bootstrap.Toast(el, { delay: 4500 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }
    $$('[data-flash]').forEach(el => toast(el.dataset.flash, el.dataset.type || 'success'));

    /* ---------- Modal hapus (dipakai index & show) ---------- */
    const delModalEl = $('#deleteModal');
    if (delModalEl) {
        const delModal = new bootstrap.Modal(delModalEl);
        const delForm = $('#deleteForm');
        document.addEventListener('click', e => {
            const btn = e.target.closest('[data-delete]');
            if (!btn) return;
            delForm.action = btn.dataset.action;
            $('#deleteName').textContent = btn.dataset.name;
            delModal.show();
        });
        delForm.addEventListener('submit', () => {
            const b = $('button[type=submit]', delForm);
            b.disabled = true;
            b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghapus';
        });
    }

    /* ---------- Salin kode ---------- */
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-copy]');
        if (!btn) return;
        navigator.clipboard?.writeText(btn.dataset.copy).then(() => toast(`Kode ${btn.dataset.copy} disalin.`));
    });

    /* ---------- Hitung naik angka statistik ---------- */
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    $$('[data-count]').forEach(el => {
        const target = +el.dataset.count;
        if (reduce || target === 0) { el.textContent = target; return; }
        const t0 = performance.now();
        const tick = now => {
            const p = Math.min((now - t0) / 700, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });

    /* ================= HALAMAN INDEX ================= */
    const list = $('#list');
    const filterForm = $('#filter-form');
    if (list && filterForm) {
        let timer, ctrl;

        const applyView = () => {
            const v = store.get('bbp-view') || 'table';
            $('#view-table')?.classList.toggle('d-none', v !== 'table');
            $('#view-grid')?.classList.toggle('d-none', v !== 'grid');
            $$('[data-view]').forEach(b => {
                const on = b.dataset.view === v;
                b.classList.toggle('active', on);
                b.setAttribute('aria-pressed', on);
            });
        };

        const syncPills = () => {
            const cur = filterForm.elements.plant_group_id.value;
            $$('.pill').forEach(p => p.classList.toggle('active', p.dataset.group === cur));
        };

        const buildUrl = () => {
            const p = new URLSearchParams(new FormData(filterForm));
            [...p.keys()].forEach(k => { if (!p.get(k)) p.delete(k); });
            const cur = new URLSearchParams(location.search);
            ['sort', 'dir'].forEach(k => { if (cur.get(k)) p.set(k, cur.get(k)); });
            const qs = p.toString();
            return filterForm.action + (qs ? '?' + qs : '');
        };

        const load = async url => {
            ctrl?.abort();
            ctrl = new AbortController();
            list.classList.add('is-loading');
            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                    signal: ctrl.signal,
                });
                if (!res.ok) throw new Error(res.status);
                list.innerHTML = await res.text();
                history.replaceState(null, '', url);
                applyView();
                syncPills();
            } catch (err) {
                if (err.name !== 'AbortError') toast('Data gagal dimuat. Periksa koneksi lalu coba lagi.', 'danger');
            } finally {
                list.classList.remove('is-loading');
            }
        };

        const reset = () => {
            filterForm.reset();
            [...filterForm.elements].forEach(el => { if (el.name) el.value = ''; });
            load(filterForm.action);
        };

        // pencarian langsung (debounce 300 ms)
        filterForm.elements.q.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => load(buildUrl()), 300);
        });
        // dropdown langsung diterapkan
        ['plant_group_id', 'treatment_id', 'province'].forEach(n =>
            filterForm.elements[n].addEventListener('change', () => { syncPills(); load(buildUrl()); }));
        filterForm.addEventListener('submit', e => { e.preventDefault(); load(buildUrl()); });

        // pil kelompok
        $('#group-pills')?.addEventListener('click', e => {
            const p = e.target.closest('.pill');
            if (!p) return;
            filterForm.elements.plant_group_id.value = p.dataset.group;
            syncPills();
            load(buildUrl());
        });

        // tombol toggle tampilan
        $$('[data-view]').forEach(b => b.addEventListener('click', () => {
            store.set('bbp-view', b.dataset.view);
            applyView();
        }));

        // klik di dalam daftar: paginasi, urut kolom, hapus filter
        document.addEventListener('click', e => {
            const link = e.target.closest('#list a.page-link, #list a[data-sort]');
            if (link && link.href) { e.preventDefault(); load(link.href); return; }

            const chip = e.target.closest('#list [data-clear]');
            if (chip) { filterForm.elements[chip.dataset.clear].value = ''; syncPills(); load(buildUrl()); return; }

            if (e.target.closest('[data-reset]')) reset();
        });

        // pintasan: tekan "/" untuk fokus ke pencarian
        document.addEventListener('keydown', e => {
            if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) {
                e.preventDefault();
                filterForm.elements.q.focus();
            }
        });

        applyView();
        syncPills();
    }

    /* ================= FORM CREATE / EDIT ================= */
    const form = $('#accession-form');
    if (form) {
        const val = id => (document.getElementById(id)?.value || '').trim();
        const required = ['code', 'name', 'plant_group_id', 'province', 'regency', 'collected_at'];
        let dirty = false, submitting = false;

        const set = (k, v) => {
            const el = $(`[data-out="${k}"]`);
            if (el) el.textContent = v || el.dataset.empty || '-';
        };

        const render = () => {
            set('code', val('code'));
            set('name', val('name'));
            const g = $('#plant_group_id');
            set('group', g.value ? g.selectedOptions[0].textContent.trim() : '');
            set('place', [val('village'), val('regency'), val('province')].filter(Boolean).join(', '));
            set('altitude', val('altitude') ? val('altitude') + ' mdpl' : '');
            set('date', val('collected_at')
                ? new Date(val('collected_at') + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
                : '');
            set('collector', val('collector'));

            const checked = $$('input[name="treatments[]"]:checked');
            $('#treat-count').textContent = checked.length;
            const box = $('[data-out-treat]');
            box.innerHTML = '';
            if (!checked.length) {
                box.innerHTML = '<span class="text-muted small">Belum ada perlakuan</span>';
            }
            checked.forEach(c => {
                const s = document.createElement('span');
                s.className = 'chip';
                s.textContent = c.dataset.label;
                box.appendChild(s);
            });

            const done = required.filter(id => val(id)).length;
            const pct = Math.round(done / required.length * 100);
            $('#progress-bar').style.width = pct + '%';
            $('#progress-text').textContent = `${done} dari ${required.length} isian wajib terisi`;
        };

        // kode: otomatis huruf besar, hanya A-Z, 0-9, tanda hubung
        const code = $('#code');
        const codeHint = $('#code-hint');
        const checkCode = () => {
            const ok = /^[A-Z0-9-]+$/.test(code.value);
            codeHint.textContent = !code.value ? 'Contoh: BBP-0009' : (ok ? 'Format kode sesuai.' : 'Gunakan huruf kapital, angka, dan tanda hubung.');
            codeHint.className = 'form-text ' + (!code.value ? '' : (ok ? 'text-success' : 'text-danger'));
        };
        code.addEventListener('input', () => {
            const pos = code.selectionStart;
            code.value = code.value.toUpperCase().replace(/\s+/g, '-').replace(/[^A-Z0-9-]/g, '');
            code.setSelectionRange(pos, pos);
            checkCode();
        });
        $('#btn-next-code')?.addEventListener('click', e => {
            code.value = e.currentTarget.dataset.code;
            code.dispatchEvent(new Event('input', { bubbles: true }));
            code.focus();
        });

        // penghitung karakter catatan
        const notes = $('#notes');
        const noteCount = $('#notes-count');
        const updateNotes = () => { noteCount.textContent = `${notes.value.length} / 1000`; };
        notes.addEventListener('input', updateNotes);

        // pilih semua / kosongkan perlakuan
        $('#treat-all')?.addEventListener('click', () => {
            $$('input[name="treatments[]"]').forEach(c => { c.checked = true; });
            dirty = true; render();
        });
        $('#treat-none')?.addEventListener('click', () => {
            $$('input[name="treatments[]"]').forEach(c => { c.checked = false; });
            dirty = true; render();
        });

        // hapus tanda error server saat pengguna mulai mengetik
        form.addEventListener('input', e => {
            e.target.classList.remove('is-invalid');
            dirty = true;
            render();
        });
        form.addEventListener('change', () => { dirty = true; render(); });

        // validasi sisi klien (server tetap menjadi penentu akhir)
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.classList.add('was-validated');
                const first = form.querySelector(':invalid');
                first?.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
                first?.focus({ preventScroll: true });
                toast('Masih ada isian wajib yang kosong atau belum sesuai.', 'danger');
                return;
            }
            submitting = true;
            const b = $('#btn-save');
            b.disabled = true;
            b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan';
        });

        window.addEventListener('beforeunload', e => {
            if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; }
        });

        checkCode();
        updateNotes();
        render();
        const firstErr = $('.is-invalid');
        if (firstErr) firstErr.scrollIntoView({ block: 'center' });
    }
})();
