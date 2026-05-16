<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SystemStatusResource;
use App\Services\System\SystemStatusService;

class SystemStatusController extends Controller
{
    public function __invoke(SystemStatusService $service): SystemStatusResource
    {
        return new SystemStatusResource($service->status());
    }
}
