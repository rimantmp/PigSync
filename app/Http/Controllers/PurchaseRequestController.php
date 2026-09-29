<?php

namespace App\Http\Controllers;

use App\Models\FeedType;
use App\Models\Medicine;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use App\Rules\ItemExists;
use App\Services\PurchaseService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function __construct(private readonly PurchaseService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $requests = PurchaseRequest::with(['branch', 'requester', 'items'])
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->orderByDesc('request_date')
            ->paginate(20)
            ->withQueryString();

        return view('purchase-requests.index', compact('requests'));
    }

    public function create()
    {
        $feedTypes = FeedType::orderBy('name')->get();
        $medicines = Medicine::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('purchase-requests.create', compact('feedTypes', 'medicines', 'units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'request_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:feed,medicine,equipment',
            'items.*.item_id' => ['required', 'integer', new ItemExists],
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.unit_id' => 'nullable|exists:units,id',
        ]);

        abort_unless(
            BranchScope::can(auth()->user(), (int) $data['branch_id']),
            404,
            'Cabang tidak ditemukan.'
        );

        $pr = $this->service->createRequest((int) $data['branch_id'], $data['items'], $data);

        return redirect()->route('purchase-requests.index')->with('status', 'Purchase Request dibuat: PR-'.$pr->id);
    }

    public function approve(PurchaseRequest $purchaseRequest)
    {
        $this->guard($purchaseRequest);

        $purchaseRequest->update(['status' => 'approved']);

        return back()->with('status', 'PR-'.$purchaseRequest->id.' disetujui.');
    }

    public function reject(PurchaseRequest $purchaseRequest)
    {
        $this->guard($purchaseRequest);

        $purchaseRequest->update(['status' => 'rejected']);

        return back()->with('status', 'PR-'.$purchaseRequest->id.' ditolak.');
    }

    /**
     * Purchase Request tanpa branch_id sendiri di URL harus tetap ikut scope.
     */
    private function guard(PurchaseRequest $request): void
    {
        abort_unless(
            BranchScope::can(auth()->user(), (int) $request->branch_id),
            404,
            'Purchase Request tidak ditemukan.'
        );
    }
}
