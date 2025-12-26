<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Services\ChangeRequestService;
use App\Http\Requests\Api\Admin\RejectChangeRequest;
use Illuminate\Http\Request;

use App\Helpers\ApiResponseHelper;
use App\Http\Resources\AccountChangeResource;

class ChangeRequestController extends Controller
{
    protected $service;

    public function __construct(ChangeRequestService $service)
    {
        $this->service = $service;
    }

    /**
     * List pending requests.
     */
    public function index()
    {
        $requests = ChangeRequest::where('status', 'pending')
            ->with(['account', 'requester'])
            ->get();

        return ApiResponseHelper::sendResponse(200, 'Pending requests retrieved', AccountChangeResource::collection($requests));
    }

    /**
     * Approve a request.
     */
    public function approve($id)
    {
        $changeRequest = ChangeRequest::findOrFail($id);

        try {
            $this->service->approveRequest($changeRequest);
            return ApiResponseHelper::sendResponse(200, 'Request approved and changes applied.');
        } catch (\Exception $e) {
            return ApiResponseHelper::sendResponse(400, $e->getMessage());
        }
    }

    /**
     * Reject a request.
     */
    public function reject(RejectChangeRequest $request, $id)
    {
        $changeRequest = ChangeRequest::findOrFail($id);
        
        $this->service->rejectRequest($changeRequest, $request->input('reason'));
        
        return ApiResponseHelper::sendResponse(200, 'Request rejected.');
    }
}