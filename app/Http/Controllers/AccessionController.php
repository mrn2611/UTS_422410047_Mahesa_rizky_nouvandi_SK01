<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccessionRequest;
use App\Http\Requests\UpdateAccessionRequest;
use App\Models\Accession;
use App\Models\CollectionOrigin;
use App\Models\PlantGroup;
use App\Models\StorageTreatment;
use App\Services\AccessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessionController extends Controller
{
    public function __construct(private AccessionService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'plant_group_id', 'treatment_id', 'province', 'sort', 'dir']);

        $data = [
            'accessions'  => $this->service->paginate($filters),
            'plantGroups' => PlantGroup::withCount('accessions')->orderBy('name')->get(),
            'treatments'  => StorageTreatment::orderBy('name')->get(),
            'filters'     => $filters,
        ];

        // Permintaan AJAX (pencarian/filter langsung) hanya butuh potongan daftar, bukan satu halaman penuh
        if ($request->ajax()) {
            return response()
                ->view('accessions._list', $data)
                ->header('Vary', 'X-Requested-With')
                ->header('Cache-Control', 'no-store');
        }

        return view('accessions.index', $data + [
            'provinces' => CollectionOrigin::select('province')->distinct()->orderBy('province')->pluck('province'),
            'stats'     => [
                'total'      => Accession::count(),
                'groups'     => PlantGroup::count(),
                'provinces'  => CollectionOrigin::distinct()->count('province'),
                'treatments' => DB::table('accession_storage_treatment')->distinct()->count('storage_treatment_id'),
            ],
        ]);
    }

    public function create()
    {
        return view('accessions.create', [
            'accession'   => new Accession(),
            'plantGroups' => PlantGroup::orderBy('name')->get(),
            'treatments'  => StorageTreatment::orderBy('name')->get(),
            'nextCode'    => $this->service->nextCode(),
        ]);
    }

    public function store(StoreAccessionRequest $request): RedirectResponse
    {
        $accession = $this->service->create($request->validated());

        return redirect()->route('accessions.show', $accession)
            ->with('success', "Aksesi {$accession->code} berhasil ditambahkan.");
    }

    public function show(Accession $accession)
    {
        $accession->load(['plantGroup', 'origin', 'treatments']);

        $related = Accession::with('origin')
            ->where('plant_group_id', $accession->plant_group_id)
            ->whereKeyNot($accession->id)
            ->latest('id')
            ->limit(4)
            ->get();

        return view('accessions.show', compact('accession', 'related'));
    }

    public function edit(Accession $accession)
    {
        $accession->load(['origin', 'treatments']);

        return view('accessions.edit', [
            'accession'   => $accession,
            'plantGroups' => PlantGroup::orderBy('name')->get(),
            'treatments'  => StorageTreatment::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAccessionRequest $request, Accession $accession): RedirectResponse
    {
        $this->service->update($accession, $request->validated());

        return redirect()->route('accessions.show', $accession)
            ->with('success', "Aksesi {$accession->code} berhasil diperbarui.");
    }

    public function destroy(Accession $accession): RedirectResponse
    {
        $code = $accession->code;
        $this->service->delete($accession);

        return redirect()->route('accessions.index')
            ->with('success', "Aksesi {$code} berhasil dihapus.");
    }
}
