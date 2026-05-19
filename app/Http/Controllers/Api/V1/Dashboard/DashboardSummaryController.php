<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\DashboardSummaryResource;
use App\Services\Dashboard\DashboardSummaryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardSummaryController extends Controller
{
    public function __construct(
        private readonly DashboardSummaryService $dashboardSummaryService
    ) {}

    public function __invoke(Request $request): DashboardSummaryResource
    {
        abort_unless($request->user()?->can('dashboard.view'), Response::HTTP_FORBIDDEN);

        return new DashboardSummaryResource($this->dashboardSummaryService->summary());
    }
}
