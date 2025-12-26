<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class ChangeRequestService
{
    /**
     * Allowed fields that a user can request to change.
     *  Prevent users from requesting to change 'balance' or 'user_id'.
     */
    protected array $allowedFields = [
        'type', 
        'status',
    ];

   
    public function createRequest(User $user, Account $account, array $changes): ChangeRequest
    {
        // 1. Authorization: User must own the account
        if ($account->user_id !== $user->id) {
            throw new Exception("Unauthorized account access.");
        }

        // 2. Filter Changes: Remove illegal fields
        $filteredChanges = array_intersect_key($changes, array_flip($this->allowedFields));

        if (empty($filteredChanges)) {
            throw new Exception("No valid fields to update.");
        }

        // 3. Create the Command Record
        return ChangeRequest::create([
            'account_id' => $account->id,
            'requester_id' => $user->id,
            'requested_changes' => $filteredChanges,
            'status' => 'pending'
        ]);
    }

    /**
     * Admin executes the command.
     */
    public function approveRequest(ChangeRequest $changeRequest)
    {
        if ($changeRequest->status !== 'pending') {
            throw new Exception("Request is not pending.");
        }

        DB::transaction(function () use ($changeRequest) {
            // 1. Load the target account
            $account = $changeRequest->account;

            // 2. Apply the changes (Execute Command)
            foreach ($changeRequest->requested_changes as $key => $value) {
                // Double-check field safety
                if (in_array($key, $this->allowedFields)) {
                    $account->{$key} = $value;
                }
            }

            $account->save();

            // 3. Mark request as approved
            $changeRequest->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);
        });

        return $changeRequest;
    }

    /**
     * Admin rejects the command.
     */
    public function rejectRequest(ChangeRequest $changeRequest, ?string $reason = null)
    {
        if ($changeRequest->status !== 'pending') {
            throw new Exception("Request is not pending.");
        }

        $changeRequest->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'processed_at' => now(),
        ]);

        return $changeRequest;
    }
}