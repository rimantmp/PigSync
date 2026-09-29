<?php

namespace App\Http\Middleware;

use App\Support\BranchScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchScope
{
    /**
     * Pastikan user punya scope cabang (selain Super Admin).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasRole('Super Admin')) {
            $scope = BranchScope::ids($user);

            // null = scope "all", [] = belum punya scope cabang sama sekali
            if ($scope === []) {
                abort(403, 'Anda belum ditetapkan ke cabang mana pun. Hubungi admin.');
            }
        }

        return $next($request);
    }
}
