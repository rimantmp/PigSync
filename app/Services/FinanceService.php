<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Catat biaya operasional.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            $expense = Expense::create([
                'branch_id' => $data['branch_id'],
                'category' => $data['category'],
                'amount' => $data['amount'],
                'spent_at' => $data['spent_at'] ?? now()->toDateString(),
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            return $expense;
        });
    }

    /**
     * Ringkasan laba-rugi sederhana per cabang/periode.
     *
     * @return array<string, mixed>
     */
    public function summary(int $branchId, ?string $from = null, ?string $to = null): array
    {
        $from ??= now()->startOfMonth()->toDateString();
        $to ??= now()->endOfMonth()->toDateString();

        $revenue = Revenue::where('branch_id', $branchId)
            ->whereBetween('received_at', [$from, $to])
            ->sum('amount');

        $expense = Expense::where('branch_id', $branchId)
            ->whereBetween('spent_at', [$from, $to])
            ->sum('amount');

        return [
            'branch_id' => $branchId,
            'from' => $from,
            'to' => $to,
            'revenue' => (float) $revenue,
            'expense' => (float) $expense,
            'profit' => (float) $revenue - (float) $expense,
        ];
    }
}
