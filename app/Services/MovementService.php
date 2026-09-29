<?php

namespace App\Services;

use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigMovement;
use App\Support\BranchScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MovementService
{
    public function __construct(
        private readonly PigService $pigs,
        private readonly PopulationService $population,
        private readonly AuditService $audit,
    ) {}

    /**
     * Pindahkan babi (BR-06, BR-07). BR-02/03: mati/dijual tidak boleh pindah.
     */
    public function move(Pig $pig, Pen $toPen, array $data): PigMovement
    {
        $user = Auth::user();

        throw_if(
            $pig->status === 'mati',
            \DomainException::class,
            'Ternak sudah mati.'
        );

        throw_if(
            $pig->status === 'dijual',
            \DomainException::class,
            'Ternak sudah terjual.'
        );

        $fromPen = $pig->pen;
        $crossBranch = $fromPen && $fromPen->branch_id !== $toPen->branch_id;

        // Antar-cabang butuh scope kedua cabang (BR + §6.2)
        if ($crossBranch) {
            $scope = BranchScope::ids($user);
            throw_if(
                $scope !== null && (! in_array($fromPen->branch_id, $scope, true) || ! in_array($toPen->branch_id, $scope, true)),
                HttpException::class,
                403,
                'Perpindahan antar-cabang butuh akses ke kedua cabang.'
            );
        }

        return DB::transaction(function () use ($pig, $fromPen, $toPen, $crossBranch, $data, $user) {
            $this->pigs->assertPenAvailable($toPen, $user, ignorePigId: $pig->id);

            $before = [
                'pen_id' => $pig->pen_id,
                'status' => $pig->status,
            ];

            $movement = PigMovement::create([
                'pig_id' => $pig->id,
                'from_pen_id' => $fromPen?->id,
                'to_pen_id' => $toPen->id,
                'moved_at' => $data['moved_at'] ?? now()->toDateString(),
                'reason' => $data['reason'],
                'type' => $crossBranch ? 'cross_branch' : ($toPen->type === 'quarantine' ? 'quarantine' : 'pen'),
                'user_id' => $user?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $pig->update(['pen_id' => $toPen->id]);

            $this->pigs->history($pig, 'pindah', (string) $fromPen?->id, (string) $toPen->id, $data['reason']);

            $this->audit->record(
                'create', 'perpindahan', $movement, null, $movement->toArray(),
                sensitive: $crossBranch,
                reason: $crossBranch ? ($data['reason'] ?? null) : null,
            );

            if ($fromPen) {
                $this->population->invalidate($fromPen);
            }
            $this->population->invalidate($toPen);

            return $movement;
        });
    }
}
