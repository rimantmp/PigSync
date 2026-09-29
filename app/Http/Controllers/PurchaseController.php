<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $requests = PurchaseRequest::with(['branch', 'items'])
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->orderByDesc('request_date')->paginate(10);

        $orders = PurchaseOrder::with(['supplier', 'branch', 'items'])
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->orderByDesc('po_date')->paginate(10);

        return view('purchase.index', compact('requests', 'orders'));
    }

    public function createOrder()
    {
        $scope = BranchScope::ids(auth()->user());

        $suppliers = Supplier::where('type', '!=', 'ternak')->orderBy('name')->get();
        $requests = PurchaseRequest::where('status', 'approved')
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->get();

        return view('purchase.create-order', compact('suppliers', 'requests'));
    }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'pr_id' => 'nullable|exists:purchase_requests,id',
            'po_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:feed,medicine,equipment',
            'items.*.item_id' => 'required|integer',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.price' => 'required|numeric|gt:0',
        ]);

        $po = $this->service->createOrder(
            (int) $data['branch_id'],
            (int) $data['supplier_id'],
            isset($data['pr_id']) ? (int) $data['pr_id'] : null,
            $data['items'],
            $data
        );

        return redirect()->route('purchase.index')->with('status', 'PO dibuat: '.$po->po_number);
    }
}
