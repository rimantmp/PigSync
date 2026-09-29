<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigBreed;
use App\Models\PigPhase;
use App\Services\PigService;
use App\Support\BranchScope;
use App\Support\RedirectsToFormModal;
use Illuminate\Http\Request;

class PigController extends Controller
{
    use RedirectsToFormModal;

    public function __construct(private readonly PigService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $pigs = Pig::with(['pen.branch', 'breed', 'phase'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w->where('code', 'like', "%{$s}%")->orWhere('tag_id', 'like', "%{$s}%")))
            ->when($request->pen_id, fn ($q, $v) => $q->where('pen_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $pens = Pen::when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))->get();
        $statuses = ['aktif', 'sakit', 'karantina', 'bunting', 'menyusui', 'dijual', 'mati', 'afkir'];

        return view('pigs.index', [
            'pigs' => $pigs,
            'pens' => $pens,
            'statuses' => $statuses,
            // Form registrasi & edit kini modal di halaman ini.
            'breeds' => PigBreed::orderBy('name')->get(),
            'phases' => PigPhase::orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        $branches = BranchScope::branches(auth()->user());
        $pens = Pen::when(BranchScope::ids(auth()->user()) !== null, fn ($q) => $q->whereIn('branch_id', BranchScope::ids(auth()->user())))->get();
        $breeds = PigBreed::orderBy('name')->get();
        $phases = PigPhase::orderBy('sort_order')->get();

        return view('pigs.create', compact('branches', 'pens', 'breeds', 'phases'));
    }

    public function store(Request $request)
    {
        $data = $this->validateForModal($request, [
            'sex' => 'required|in:jantan,betina',
            'breed_id' => 'nullable|exists:pig_breeds,id',
            'birth_date' => 'required|date|before_or_equal:today',
            'origin_type' => 'required|in:internal,eksternal',
            'origin_ref' => 'nullable|integer',
            'sire_id' => 'nullable|exists:pigs,id',
            'dam_id' => 'nullable|exists:pigs,id',
            'pen_id' => 'required|exists:pens,id',
            'phase_id' => 'nullable|exists:pig_phases,id',
            'initial_weight' => 'nullable|numeric|min:0.5|max:150',
            'tag_id' => 'nullable|string|max:50',
            'rfid' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $pig = $this->service->register($data);

        // Dari form modal, user tetap di halaman daftar. Redirect ke detail
        // hanya berlaku untuk form halaman penuh.
        if ($request->filled('form_modal')) {
            return redirect()
                ->route('pigs.index')
                ->with('status', 'Ternak terdaftar: '.$pig->code);
        }

        return redirect()->route('pigs.show', $pig)->with('status', 'Ternak terdaftar: '.$pig->code);
    }

    public function show(Pig $pig)
    {
        $pig->load([
            'pen.branch', 'breed', 'phase', 'sire', 'dam',
            'histories' => fn ($q) => $q->orderByDesc('event_date'),
            'weights', 'movements.fromPen', 'movements.toPen',
            'healthRecords.disease', 'healthRecords.medicine',
            'deaths',
        ]);

        return view('pigs.show', compact('pig'));
    }

    public function edit(Pig $pig)
    {
        $pens = Pen::all();
        $breeds = PigBreed::orderBy('name')->get();
        $phases = PigPhase::orderBy('sort_order')->get();

        return view('pigs.edit', compact('pig', 'pens', 'breeds', 'phases'));
    }

    public function update(Request $request, Pig $pig)
    {
        $data = $this->validateForModal($request, [
            'tag_id' => 'nullable|string|max:50',
            'rfid' => 'nullable|string|max:50',
            'sex' => 'required|in:jantan,betina',
            'breed_id' => 'nullable|exists:pig_breeds,id',
            'phase_id' => 'nullable|exists:pig_phases,id',
            'notes' => 'nullable|string',
        ]);

        $this->service->update($pig, $data);

        if ($request->filled('form_modal')) {
            return redirect()
                ->route('pigs.index')
                ->with('status', 'Data ternak diperbarui.');
        }

        return redirect()->route('pigs.show', $pig)->with('status', 'Data ternak diperbarui.');
    }
}
