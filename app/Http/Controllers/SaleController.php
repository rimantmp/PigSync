<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Pig;
use App\Models\Sale;
use App\Services\SaleService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $sales = Sale::with(['customer', 'branch', 'items.pig'])
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->orderByDesc('sale_date')
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $scope = BranchScope::ids(auth()->user());

        $customers = Customer::orderBy('name')->get();
        $pigs = Pig::whereNotIn('status', ['mati', 'dijual'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderBy('code')->get();

        return view('sales.create', compact('customers', 'pigs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'sale_date' => 'required|date|before_or_equal:today',
            'payment_status' => 'required|in:belum_bayar,lunas',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.pig_id' => 'required|exists:pigs,id',
            'items.*.weight' => 'required|numeric|gt:0',
            'items.*.price_per_kg' => 'required|numeric|gt:0',
        ]);

        $sale = $this->service->sell(
            (int) $data['branch_id'],
            (int) $data['customer_id'],
            $data['items'],
            $data
        );

        return redirect()->route('sales.index')->with('status', 'Penjualan tercatat: '.$sale->invoice_number);
    }
}
