<?php

namespace App\Helpers;

class ApiResponseHelper
{
    public const PER_PAGE = 26;
    public const PAGINATE_RESOURCE = [10, 25, 50, 100];

    public static function sendResponse($code = 200, $msg = 'ok', $data = null)
    {
        $response = ['message' => $msg, 'data' => $data];
        return response()->json($response, $code);
    }

    public static function paginateResource($resource, $key)
    {
        $resource = $resource->response()->getData();
        return [$key => $resource->data, 'next' => $resource->links->next];
    }
}
