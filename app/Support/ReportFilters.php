<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Filter laporan yang sudah tervalidasi dan sudah ter-scope.
 *
 * Dipisah dari Request supaya query laporan tidak terikat HTTP layer.
 */
final class ReportFilters
{
    /**
     * @param  array<int>|null  $branchIds  null = semua cabang (Super Admin)
     */
    public function __construct(
        public readonly ?int $branchId = null,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        public readonly ?array $branchIds = null,
    ) {}

    public static function fromRequest(Request $request, ?User $user = null): self
    {
        $data = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        return new self(
            branchId: isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            from: $data['from'] ?? null,
            to: $data['to'] ?? null,
            branchIds: BranchScope::ids($user ?? $request->user()),
        );
    }

    public function describe(): string
    {
        $parts = [];

        if ($this->branchId !== null) {
            $parts[] = 'Cabang #'.$this->branchId;
        }

        if ($this->from || $this->to) {
            $parts[] = ($this->from ?: 'awal').' s.d. '.($this->to ?: 'sekarang');
        }

        return $parts === [] ? 'Seluruh data' : implode(' — ', $parts);
    }
}
