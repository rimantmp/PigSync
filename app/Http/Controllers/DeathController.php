<?php

namespace App\Http\Controllers;

use App\Models\Death;
use App\Models\Disease;
use App\Models\Pig;
use App\Services\DeathService;
use App\Support\BranchScope;
use App\Support\RedirectsToFormModal;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DeathController extends Controller
{
    use RedirectsToFormModal;

    public function __construct(private readonly DeathService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $deaths = Death::with(['pig', 'pen.branch', 'suspectedDisease'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderByDesc('died_at')
            ->paginate(20)
            ->withQueryString();

        return view('deaths.index', [
            'deaths' => $deaths,
            'pigs' => $this->livingPigs($scope),
            'diseases' => Disease::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $scope = BranchScope::ids(auth()->user());

        return view('deaths.create', [
            'pigs' => $this->livingPigs($scope),
            'diseases' => Disease::orderBy('name')->get(),
        ]);
    }

    /**
     * Ternak yang masih hidup — belum mati dan belum terjual.
     *
     * @param  array<int>|null  $scope
     * @return Collection<int, Pig>
     */
    private function livingPigs(?array $scope)
    {
        return Pig::whereNotIn('status', ['mati', 'dijual'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderBy('code')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $this->validateForModal($request, [
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
