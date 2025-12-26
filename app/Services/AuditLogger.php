<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Log an audit event.
     *
     * @param string $action The action being performed (e.g., 'transaction.created', 'account.frozen').
     * @param Model|null $model The model associated with the action, if any.
     * @param array|null $payload Additional data to log.
     * @return void
     */
    public function log(string $action, ?Model $model = null, ?array $payload = []): void
    {
        $user = Auth::user();

        $data = [
            'action' => $action,
            'user_id' => $user ? $user->id : 'system',
            'user_role' => $user ? ($user->isAdmin() ? 'admin' : 'user') : 'system',
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->getKey() : null,
            'payload' => $payload,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ];

        Log::channel('audit')->info($action, $data);
    }
}
