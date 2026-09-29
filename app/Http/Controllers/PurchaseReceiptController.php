<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Services\PurchaseService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class PurchaseReceiptController extends Controller
{
    public function __construct(private readonly PurchaseService $service) {}

    public function create(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $orders = PurchaseOrder::with(['supplier', 'items'])
            ->whereIn('status', ['draft', 'received'])
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->orderByDesc('po_date')
            ->get();

        return view('purchase-receipts.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'po_id' => 'required|exists:purchase_orders,id',
            'received_at' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
            'received_qty' => 'required|array',
            'received_qty.*' => 'required|numeric|gte:0',
        ]);

        $po = PurchaseOrder::findOrFail($data['po_id']);

        abort_unless(
            BranchScope::can(auth()->user(), (int) $po->branch_id),
            404,
            'Purchase Order tidak ditemukan.'
        );

        $receipt = $this->service->receive($po, $data);

        return redirect()->route('purchase.index')->with('status', 'Barang diterima (receipt #'.$receipt->id.').');
    }
}
