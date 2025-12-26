<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'from_account_id',
        'to_account_id',
        'amount',
        'type',
        'status',
        'payment_provider',
        'provider_transaction_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Auto-generate UUID on creation.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // --- Relationships ---

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    // --- Scopes ---

    /**
     * Scope for the "Retry" Logic (Observer Pattern listener will use this).
     */
    public function scopeFailedWaitingForFunds($query)
    {
        return $query->where('status', 'failed_waiting_for_funds');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }
}