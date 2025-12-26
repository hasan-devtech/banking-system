<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\ChangeRequestService;
use App\Http\Requests\Api\Account\StoreChangeRequest;
use Illuminate\Http\Request;

use App\Helpers\ApiResponseHelper;
use App\Http\Resources\AccountChangeResource;
use Illuminate\Support\Facades\Gate;

class AccountChangeController extends Controller
{
    protected $service;

    public function __construct(ChangeRequestService $service)
    {
        $this->service = $service;
    }


    public function store(StoreChangeRequest $request, $accountId)
    {
        try {
            $account = Account::findOrFail($accountId);

            Gate::authorize('requestChange', $account);

            $changeRequest = $this->service->createRequest(
                $request->user(),
                $account,
                $request->input('changes')
            );

            return ApiResponseHelper::sendResponse(202, 'Change request submitted for approval', new AccountChangeResource($changeRequest));

        } catch (\Exception $e) {
            return ApiResponseHelper::sendResponse(403, $e->getMessage());
        }
    }
}
