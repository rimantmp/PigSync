<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use App\Support\BranchScope;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FinanceController extends Controller
{
    public function __construct(private readonly FinanceService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());
        $user = auth()->user();

        $branchId = (int) ($request->branch_id ?? BranchScope::branches($user)->first()?->id ?? 0);

        throw_if($branchId === 0, \DomainException::class, 'Belum ada cabang.');

        $scopeOk = $scope === null || in_array($branchId, $scope, true);
        throw_unless($scopeOk, HttpException::class, 403, 'Cabang di luar scope.');

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->endOfMonth()->toDateString();

        $summary = $this->service->summary($branchId, $from, $to);
        $branches = BranchScope::branches($user);

        return view('finance.index', compact('summary', 'branches'));
    }
}
