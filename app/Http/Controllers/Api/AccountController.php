<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use App\Http\Requests\Api\Account\StoreAccountRequest;
use Illuminate\Http\Request;

use App\Models\Account;
use Illuminate\Support\Facades\Gate;

use App\Helpers\ApiResponseHelper;
use App\Http\Resources\AccountResource;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * List all accounts in hierarchical structure.
     */
    public function index(Request $request)
    {
        // For index, we usually return list of accessible items. 
        // The service already filters by $user.
        // potentially could add Gate check here if needed, but existing logic is fine:
        $hierarchy = $this->accountService->getUserHierarchy($request->user());
        // Assuming hierarchy is a collection, we transform it. 
        // If it's a tree array, we might send it directly as data, but Resource collection is safer if it's Eloquent models.
        return ApiResponseHelper::sendResponse(200, 'User hierarchy retrieved', $hierarchy);
    }

    /**
     * Create a new account.
     */
    public function store(StoreAccountRequest $request)
    {
        Gate::authorize('create', Account::class);

        $validated = $request->validated();

        try {
            $account = $this->accountService->createAccount($request->user(), $validated);
            
            return ApiResponseHelper::sendResponse(201, 'Account created successfully', new AccountResource($account));

        } catch (\Exception $e) {
            return ApiResponseHelper::sendResponse(400, $e->getMessage());
        }
    }

    /**
     * Show single account details.
     */
    public function show(Request $request, $id)
    {
        // Find the account globally
        $account = Account::with('allChildren')->findOrFail($id);

        // Authorize view access
        Gate::authorize('view', $account);
        
        // Append the virtual balance to the JSON response
        $account->append('consolidated_balance');

        return ApiResponseHelper::sendResponse(200, 'Account details retrieved', new AccountResource($account));
    }
}