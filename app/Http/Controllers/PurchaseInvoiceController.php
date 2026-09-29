<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Services\PurchaseService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    public function __construct(private readonly PurchaseService $service) {}

    public function create()
    {
        $scope = BranchScope::ids(auth()->user());

        $orders = PurchaseOrder::where('status', 'received')
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->orderByDesc('po_date')
            ->get();

        return view('purchase-invoices.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'po_id' => 'required|exists:purchase_orders,id',
            'invoice_date' => 'required|date|before_or_equal:today',
            'total' => 'required|numeric|gt:0',
        ]);

        $po = PurchaseOrder::findOrFail($data['po_id']);

        abort_unless(
            BranchScope::can(auth()->user(), (int) $po->branch_id),
            404,
            'Purchase Order tidak ditemukan.'
        );

        $invoice = $this->service->invoice($po, $data);

        return redirect()->route('purchase.index')->with('status', 'Invoice dibuat: '.$invoice->invoice_number);
    }
}
