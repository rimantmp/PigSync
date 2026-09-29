<?php

namespace App\Http\Controllers;

use App\Models\Death;
use App\Models\Disease;
use App\Models\Pig;
use App\Services\DeathService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class DeathController extends Controller
{
    public function __construct(private readonly DeathService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $deaths = Death::with(['pig', 'pen.branch', 'suspectedDisease'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderByDesc('died_at')
            ->paginate(20)
            ->withQueryString();

        return view('deaths.index', compact('deaths'));
    }

    public function create()
    {
        $scope = BranchScope::ids(auth()->user());

        $pigs = Pig::whereNotIn('status', ['mati', 'dijual'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderBy('code')->get();

        $diseases = Disease::orderBy('name')->get();

        return view('deaths.create', compact('pigs', 'diseases'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pig_id' => 'required|exists:pigs,id',
            'died_at' => 'required|date|before_or_equal:today',
            'cause' => 'required|string|max:255',
            'suspected_disease_id' => 'nullable|exists:diseases,id',
            'disposal' => 'nullable|in:kubur,bakar,afkir_jual',
            'estimated_loss' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $pig = Pig::findOrFail($data['pig_id']);

        $this->service->record($pig, $data);

        return redirect()->route('deaths.index')->with('status', 'Kematian tercatat.');
    }
}
