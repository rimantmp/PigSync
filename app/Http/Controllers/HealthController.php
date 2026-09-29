<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\HealthRecord;
use App\Models\Medicine;
use App\Models\Pig;
use App\Services\HealthService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __construct(private readonly HealthService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $records = HealthRecord::with('pig.pen.branch', 'disease', 'medicine')
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->orderByDesc('checked_at')
            ->paginate(20)
            ->withQueryString();

        return view('health.index', compact('records'));
    }

    public function create()
    {
        $scope = BranchScope::ids(auth()->user());
        $pigs = Pig::whereNotIn('status', ['mati'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderBy('code')->get();
        $diseases = Disease::orderBy('name')->get();
        $medicines = Medicine::orderBy('name')->get();

        return view('health.create', compact('pigs', 'diseases', 'medicines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pig_id' => 'required|exists:pigs,id',
            'checked_at' => 'required|date|before_or_equal:today',
            'symptoms' => 'nullable|string',
            'disease_id' => 'nullable|exists:diseases,id',
            'diagnosis' => 'nullable|string',
            'medicine_id' => 'nullable|exists:medicines,id',
            'dose' => 'nullable|string|max:100',
            'route' => 'nullable|in:injeksi,oral,topikal',
            'status' => 'required|in:sakit,karantina',
            'vet' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $pig = Pig::findOrFail($data['pig_id']);
        $this->service->record($pig, $data);

        return redirect()->route('health.index')->with('status', 'Pemeriksaan kesehatan tersimpan.');
    }

    public function recover(Pig $pig)
    {
        $this->service->recover($pig);

        return back()->with('status', 'Status ternak kembali aktif.');
    }
}
