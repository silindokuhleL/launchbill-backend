<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthenticatedUserResource;
use App\Http\Resources\Auth\AuthSessionResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): AuthSessionResource
    {
        return new AuthSessionResource(
            $this->authService->register($request->validated())
        );
    }

    public function login(LoginRequest $request): AuthSessionResource
    {
        return new AuthSessionResource(
            $this->authService->login($request->validated())
        );
    }

    public function me(Request $request): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource(
            $this->authService->loadAuthenticatedUser($request->user())
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
