<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

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
     * List all pending transactions requiring attention.
     */
    public function index()
    {
        $pending = Transaction::pendingApproval()
            ->with(['fromAccount.user', 'toAccount'])
            ->orderBy('created_at', 'asc')
            ->get();
        return ApiResponseHelper::sendResponse(200, 'Pending transactions retrieved', TransactionResource::collection($pending));
    }

    /**
     * Approve a specific transaction.
     */
    public function approve($id)
    {
        $transaction = Transaction::findOrFail($id);

        try {
            $result = $this->transactionService->approveTransaction($transaction);
            
            return ApiResponseHelper::sendResponse(200, 'Transaction approved and executed successfully', new TransactionResource($result));

        } catch (\Exception $e) {
            return ApiResponseHelper::sendResponse(400, $e->getMessage());
        }
    }

    /**
     * Reject a specific transaction.
     */
    public function reject($id)
    {
        $transaction = Transaction::findOrFail($id);

        try {
            $result = $this->transactionService->rejectTransaction($transaction);
            
            return ApiResponseHelper::sendResponse(200, 'Transaction rejected', new TransactionResource($result));

        } catch (\Exception $e) {
            return ApiResponseHelper::sendResponse(400, $e->getMessage());
        }
    }
}