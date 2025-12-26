<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'requester_id',
        'requested_changes',
        'status',
        'admin_notes',
        'processed_at'
    ];

    protected $casts = [
        'requested_changes' => 'array', // Automatically serialize JSON to PHP Array
        'processed_at' => 'datetime',
    ];

    // --- Relationships ---

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Command Execution Logic: Apply the changes to the account.
     */
    public function execute()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $account = $this->account;
        
        foreach ($this->requested_changes as $key => $value) {
            // Security: Ensure we only update valid fields
            if (in_array($key, $account->getFillable())) {
                $account->$key = $value;
            }
        }
        
        return $account->save();
    }
}
