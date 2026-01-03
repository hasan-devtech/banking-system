<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\TransactionService;
use App\Http\Requests\Api\Transaction\DepositRequest;
use App\Http\Requests\Api\Transaction\StoreTransferRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Gate;

use App\Helpers\ApiResponseHelper;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle a Deposit (Cash In).
     * Route: POST /api/transactions/deposit
     */
    public function deposit(DepositRequest $request)
    {
        $validated = $request->validated();

        // 1. Fetch Account
        $account = Account::findOrFail($validated['account_id']);

        // 2. Authorize Deposit
        Gate::authorize('deposit', $account);

        try {
            $transaction = $this->transactionService->deposit($account, $validated['amount']);

            return ApiResponseHelper::sendResponse(200, 'Deposit processed successfully', new TransactionResource($transaction));

        } catch (\Exception $e) {
            return ApiResponseHelper::sendResponse(400, $e->getMessage());
        }
    }

    /**
     * Handle an Internal Transfer.
     * Route: POST /api/transactions/transfer
     */
    public function transfer(StoreTransferRequest $request)
    {
        $validated = $request->validated();

        // 1. Fetch Source Account
        $fromAccount = Account::findOrFail($validated['from_account_id']);

        // 2. Fetch Target Account
        $toAccount = Account::where('account_number', $validated['to_account_number'])->firstOrFail();

        // Prevent self-transfer loop if needed
        if ($fromAccount->id === $toAccount->id) {
            return ApiResponseHelper::sendResponse(400, 'Cannot transfer to the same account');
        }

        // 3. Authorize Withdraw/Transfer from Source
        Gate::authorize('withdraw', $fromAccount);

        try {
            // 4. Delegate to Service (Facade)
            $transaction = $this->transactionService->createTransfer(
                $fromAccount,
                $toAccount,
                $validated['amount']
            );

            // 5. Return Contextual Response
            if ($transaction->status === 'pending_approval') {
                return ApiResponseHelper::sendResponse(202, 'Transfer is large and requires manager approval.', new TransactionResource($transaction));
            }

            return ApiResponseHelper::sendResponse(200, 'Transfer completed successfully', [
                'transaction' => new TransactionResource($transaction),
                'new_balance' => $fromAccount->fresh()->balance
            ]);

        } catch (\Exception $e) {
            // catches "Insufficient Funds" or "Frozen Account" errors
            return ApiResponseHelper::sendResponse(400, $e->getMessage());
        }
    }
}