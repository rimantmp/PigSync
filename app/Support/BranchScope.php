<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Collection;

class BranchScope
{
    /**
     * Daftar id cabang yang boleh diakses user.
     *
     * @return array<int>|null null = semua cabang (super admin / scope all)
     */
    public static function ids(?User $user): ?array
    {
        if (! $user) {
            return [];
        }

        if ($user->hasRole('Super Admin')) {
            return null;
        }

        // null = seluruh cabang. Array kosong = tidak ada cabang sama sekali,
        // jadi jangan pakai blank() — blank([]) true dan akan membuka semua cabang.
        $scope = $user->branch_scope;

        if ($scope === null) {
            return null;
        }

        $ids = is_array($scope) ? $scope : json_decode($scope, true);

        return collect($ids)->map(fn ($id) => (int) $id)->values()->all();
    }

    /**
     * Apakah user dapat mengakses cabang tertentu.
     */
    public static function can(User $user, int $branchId): bool
    {
        $ids = static::ids($user);

        return $ids === null || in_array($branchId, $ids, true);
    }

    /**
     * Koleksi cabang yang visible bagi user.
     */
    public static function branches(?User $user): Collection
    {
        $ids = static::ids($user);

        return $ids === null ? Branch::all() : Branch::whereIn('id', $ids)->get();
    }
}
