<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PurchaseInvoice;
use App\Models\Revenue;
use App\Models\Sale;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function create()
    {
        $scope = BranchScope::ids(auth()->user());

        $invoices = PurchaseInvoice::where('status', 'belum_bayar')
            ->whereIn('po_id', fn ($q) => $q->select('id')->from('purchase_orders')->when($scope !== null, fn ($w) => $w->whereIn('branch_id', $scope)))
            ->get();

        $sales = Sale::where('payment_status', 'belum_bayar')
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->get();

        return view('payments.create', compact('invoices', 'sales'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payable_type' => 'required|in:invoice,sale',
            'payable_id' => 'required|integer',
            'amount' => 'required|numeric|gt:0',
            'method' => 'required|in:tunai,transfer,tempo',
            'paid_at' => 'required|date|before_or_equal:today',
        ]);

        $payableType = $data['payable_type'] === 'invoice' ? PurchaseInvoice::class : Sale::class;
        $payable = $payableType::findOrFail($data['payable_id']);

        // Invoice tidak punya branch_id sendiri — cabangnya lewat relasi PO.
        $branchId = $payable instanceof PurchaseInvoice
            ? $payable->order?->branch_id
            : $payable->branch_id;

        abort_if(
            $branchId === null || ! BranchScope::can(auth()->user(), (int) $branchId),
            404,
            'Data pembayaran tidak ditemukan.'
        );

        Payment::create([
            'payable_type' => $payableType,
            'payable_id' => $payable->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'paid_at' => $data['paid_at'],
            'status' => 'lunas',
            'user_id' => auth()->id(),
        ]);

        if ($payable instanceof PurchaseInvoice) {
            $payable->update(['status' => 'lunas']);
            $payable->order?->update(['status' => 'lunas']);
        } else {
            $payable->update(['payment_status' => 'lunas']);
            Revenue::firstOrCreate(
                ['reference_type' => Sale::class, 'reference_id' => $payable->id],
                [
                    'branch_id' => $payable->branch_id,
                    'source' => 'penjualan',
                    'amount' => $payable->total,
                    'received_at' => $data['paid_at'],
                    'user_id' => auth()->id(),
                ]
            );
        }

        return redirect()->route('purchase.index')->with('status', 'Pembayaran tercatat.');
    }
}
