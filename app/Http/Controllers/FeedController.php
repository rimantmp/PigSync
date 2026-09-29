<?php

namespace App\Http\Controllers;

use App\Models\FeedType;
use App\Models\Pen;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Services\StockService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __construct(private readonly StockService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $stocks = Stock::with(['warehouse.branch'])
            ->where('item_type', 'feed')
            ->when($scope !== null, fn ($q) => $q->whereHas('warehouse', fn ($w) => $w->whereIn('branch_id', $scope)))
            ->get();

        $pens = Pen::when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))->get();
        $feedTypes = FeedType::orderBy('name')->get();

        return view('feeds.index', compact('stocks', 'pens', 'feedTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'item_id' => 'required|exists:feed_types,id',
            'qty' => 'required|numeric|gt:0',
            'direction' => 'required|in:in,out',
            'pen_id' => 'nullable|required_if:direction,out|exists:pens,id',
            'notes' => 'nullable|string',
        ]);

        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $unit = FeedType::find($data['item_id'])?->unit_id;

        if ($data['direction'] === 'in') {
            $this->service->receive($warehouse, 'feed', $data['item_id'], $data['qty'], [
                'unit_id' => $unit,
                'notes' => $data['notes'] ?? null,
            ]);
            $msg = 'Pakan masuk.';
        } else {
            $this->service->issue($warehouse, 'feed', $data['item_id'], $data['qty'], [
                'unit_id' => $unit,
                'pen_id' => $data['pen_id'],
                'txn_type' => 'issue',
                'notes' => $data['notes'] ?? 'Distribusi pakan',
            ]);
            $msg = 'Distribusi pakan ke kandang.';
        }

        return back()->with('status', $msg);
    }
}
