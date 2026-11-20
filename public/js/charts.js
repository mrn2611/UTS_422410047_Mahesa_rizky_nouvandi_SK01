(() => {
    'use strict';
    const dataEl = document.getElementById('chart-data');
    if (!dataEl) return;

    // Jika Chart.js gagal dimuat (mis. tanpa internet), tampilkan pesan, jangan biarkan kotak kosong
    if (!window.Chart) {
        document.querySelectorAll('.chart-box').forEach(b => {
            b.innerHTML = '<p class="chart-empty">Grafik tidak dapat dimuat. Periksa koneksi internet.</p>';
        });
        return;
    }

    const d = JSON.parse(dataEl.textContent);
    const indexUrl = dataEl.dataset.indexUrl;
    
    // Palet warna cerah & modern sesuai tema pertanian pusaka yang hidup
    const PALETTE = ['#10b981', '#f59e0b', '#6366f1', '#f43f5e', '#84cc16', '#06b6d4'];
    const PALETTE_HOVER = ['#059669', '#d97706', '#4f46e5', '#e11d48', '#65a30d', '#0891b2'];
    
    const EMERALD = '#10b981', EMERALD_DARK = '#059669';
    const AMBER = '#f59e0b', AMBER_DARK = '#d97706';
    const GRID = '#eef2f0';

    Chart.defaults.font.family = "'Source Sans 3', system-ui, sans-serif";
    Chart.defaults.font.size = 13;
    Chart.defaults.color = '#64748b';
    
    // Konfigurasi default tooltip interaktif & modern
    Chart.defaults.plugins.tooltip = {
        backgroundColor: '#0f172a',
        titleColor: '#f8fafc',
        bodyColor: '#e2e8f0',
        titleFont: { family: "'Source Sans 3', sans-serif", weight: 'bold', size: 13 },
        bodyFont: { family: "'Source Sans 3', sans-serif", size: 13 },
        padding: 10,
        cornerRadius: 8,
        displayColors: true,
        boxPadding: 4,
        boxWidth: 8,
        boxHeight: 8,
        usePointStyle: true,
    };

    if (matchMedia('(prefers-reduced-motion: reduce)').matches) {
        Chart.defaults.animation = false;
    } else {
        Chart.defaults.animation = {
            duration: 750,
            easing: 'easeOutQuart'
        };
    }

    const sum = a => a.reduce((s, n) => s + n, 0);
    const pointer = (evt, els) => { evt.native.target.style.cursor = els.length ? 'pointer' : 'default'; };
    const go = params => { location.href = indexUrl + '?' + new URLSearchParams(params).toString(); };

    const draw = (id, config) => {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        if (sum(config.data.datasets[0].data) === 0) {
            canvas.parentElement.innerHTML = '<p class="chart-empty"><i class="bi bi-bar-chart me-2"></i>Belum ada data untuk ditampilkan.</p>';
            return;
        }
        return new Chart(canvas, config);
    };

    // Teks total di tengah diagram donat
    const centerText = {
        id: 'centerText',
        afterDraw(chart) {
            const { ctx, chartArea: { left, right, top, bottom } } = chart;
            const x = (left + right) / 2, y = (top + bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#0f172a';
            ctx.font = "700 32px Bitter, Georgia, serif";
            ctx.fillText(sum(chart.data.datasets[0].data), x, y - 8);
            ctx.font = "600 12px 'Source Sans 3', sans-serif";
            ctx.fillStyle = '#94a3b8';
            ctx.fillText('TOTAL AKSESI', x, y + 18);
            ctx.restore();
        },
    };

    // 1. Donat: Kelompok Tanaman
    draw('chart-group', {
        type: 'doughnut',
        data: {
            labels: d.group.labels,
            datasets: [{
                data: d.group.values,
                backgroundColor: d.group.ids.map(i => PALETTE[i % 6]),
                hoverBackgroundColor: d.group.ids.map(i => PALETTE_HOVER[i % 6]),
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 10,
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 14,
                        font: { size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: c => ` ${c.label}: ${c.raw} aksesi`
                    }
                }
            },
            onHover: pointer,
            onClick: (e, els) => { if (els.length) go({ plant_group_id: d.group.ids[els[0].index] }); },
        },
        plugins: [centerText],
    });

    // Batang horizontal yang dipakai ulang (Provinsi & Perlakuan)
    const barH = (id, block, baseColor, hoverColor, onPick) => draw(id, {
        type: 'bar',
        data: {
            labels: block.labels,
            datasets: [{
                data: block.values,
                backgroundColor: baseColor,
                hoverBackgroundColor: hoverColor,
                borderRadius: 6,
                maxBarThickness: 26,
            }]
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: c => ` ${c.parsed.x} spesimen aksesi`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: GRID }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { weight: '600' } }
                },
            },
            onHover: onPick ? pointer : undefined,
            onClick: (e, els) => { if (onPick && els.length) onPick(els[0].index); },
        },
    });

    // 2. Provinsi (klik -> filter provinsi)
    barH('chart-province', d.province, EMERALD, EMERALD_DARK, i => go({ province: d.province.labels[i] }));

    // 3. Perlakuan (klik -> filter perlakuan)
    barH('chart-treatment', d.treatment, AMBER, AMBER_DARK, i => go({ treatment_id: d.treatment.ids[i] }));

    // 4. Ketinggian (Elevasi)
    const altColors = ['#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#94a3b8'];
    const altHover = ['#059669', '#0891b2', '#2563eb', '#7c3aed', '#64748b'];
    draw('chart-altitude', {
        type: 'bar',
        data: {
            labels: d.altitude.labels,
            datasets: [{
                data: d.altitude.values,
                backgroundColor: altColors,
                hoverBackgroundColor: altHover,
                borderRadius: 6,
                maxBarThickness: 48,
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: c => ` ${c.parsed.y} aksesi`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: GRID }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { weight: '600' } }
                },
            },
        },
    });

    // 5. Tren Bulanan dengan Gradient Area
    const monthCanvas = document.getElementById('chart-month');
    let monthGradient = 'rgba(16, 185, 129, 0.15)';
    if (monthCanvas) {
        const ctx = monthCanvas.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 260);
        grad.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
        grad.addColorStop(1, 'rgba(16, 185, 129, 0.01)');
        monthGradient = grad;
    }

    draw('chart-month', {
        type: 'line',
        data: {
            labels: d.month.labels,
            datasets: [{
                data: d.month.values,
                borderColor: '#10b981',
                borderWidth: 2.5,
                backgroundColor: monthGradient,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: '#10b981',
                pointHoverBackgroundColor: '#059669',
                pointBorderColor: '#ffffff',
                pointHoverBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverBorderWidth: 3,
            }],
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: c => ` ${c.parsed.y} aksesi dikumpulkan`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: GRID }
                },
                x: {
                    grid: { display: false }
                },
            },
        },
    });
})();
