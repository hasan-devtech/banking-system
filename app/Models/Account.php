<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\States\Account\AccountState;
use App\States\Account\ActiveState;
use App\States\Account\ClosedState;
use App\States\Account\FrozenState;
use App\States\Account\SuspendedState;
use InvalidArgumentException;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'parent_id',
        'account_number',
        'type',
        'status',
        'balance',
        'currency',
        'interest_strategy'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];



    /**
     * The State Pattern Accessor.
     * Usage: $account->state->canWithdraw();
     */

    public function getStateAttribute(): AccountState
    {
        return match ($this->status) {
            'active' => new ActiveState(),
            'frozen' => new FrozenState(),
            'suspended' => new SuspendedState(),
            'closed' => new ClosedState(),
            default => throw new InvalidArgumentException("Invalid account status: {$this->status}"),
        };
    }

    /**
     * Helper to transition state securely.
     */
    public function transitionTo(string $newStatus): void
    {
        // Add validation logic here if needed (e.g., cannot go from 'closed' to 'active')
        if ($this->status === 'closed') {
             throw new \Exception("Cannot reopen a closed account.");
        }

        $this->status = $newStatus;
        $this->save();
    }


    // --- Relationships ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Composite Pattern: Get the parent account.
     */
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Composite Pattern: Get direct child accounts.
     */
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Composite Pattern: Recursive children (grandchildren, etc).
     * Useful for eager loading the whole tree.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function transactionsSent()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function transactionsReceived()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    public function changeRequests()
    {
        return $this->hasMany(ChangeRequest::class);
    }

    // --- Accessors ---

    /**
     * Helper to get total balance including children (Virtual Balance).
     * If this account is a "Parent", it sums its children.
     * If it is a "Child", it returns its own balance.
     */
    public function getConsolidatedBalanceAttribute()
    {
        if ($this->children()->count() === 0) {
            return $this->balance;
        }

        // Note: For very deep trees, handle this calculation via DB query for performance.
        return $this->children->sum(fn($child) => $child->consolidated_balance);
    }
}