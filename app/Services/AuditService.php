<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Catat aksi audit (BR-12).
     *
     * @param  array<mixed>|null  $before
     * @param  array<mixed>|null  $after
     */
    public function record(
        string $action,
        string $module,
        ?Model $entity = null,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
        bool $sensitive = false,
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();

        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'entity_type' => $entity ? $entity::class : null,
            'entity_id' => $entity?->id,
            'before' => $before,
            'after' => $after,
            'reason' => $reason,
            'sensitive' => $sensitive,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'at' => now(),
        ]);
    }

    /**
     * Shortcut untuk aksi sensitif yang wajib punya alasan.
     */
    public function sensitive(string $action, string $module, Model $entity, array $before, array $after, string $reason): AuditLog
    {
        throw_if(
            blank($reason),
            \InvalidArgumentException::class,
            'Alasan wajib diisi untuk aksi sensitif.'
        );

        return $this->record($action, $module, $entity, $before, $after, $reason, sensitive: true);
    }
}
