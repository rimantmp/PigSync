<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigMovement;
use App\Services\MovementService;
use App\Support\BranchScope;
use App\Support\RedirectsToFormModal;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MovementController extends Controller
{
    use RedirectsToFormModal;

    public function __construct(private readonly MovementService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $movements = PigMovement::with(['pig', 'fromPen', 'toPen'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->orderByDesc('moved_at')
            ->paginate(20)
            ->withQueryString();

        return view('movements.index', [
            'movements' => $movements,
            'pigs' => $this->movablePigs($scope),
            'pens' => Pen::when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $scope = BranchScope::ids(auth()->user());

        return view('movements.create', [
            'pigs' => $this->movablePigs($scope),
            'pens' => Pen::when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))->orderBy('name')->get(),
        ]);
    }

    /**
     * Ternak yang masih boleh dipindahkan.
     *
     * @param  array<int>|null  $scope
     * @return Collection<int, Pig>
     */
    private function movablePigs(?array $scope)
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
            'to_pen_id' => 'required|exists:pens,id',
            'moved_at' => 'required|date|before_or_equal:today',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $pig = Pig::findOrFail($data['pig_id']);
        $toPen = Pen::findOrFail($data['to_pen_id']);

        $this->service->move($pig, $toPen, $data);

        return redirect()->route('movements.index')->with('status', 'Perpindahan tercatat.');
    }
}
