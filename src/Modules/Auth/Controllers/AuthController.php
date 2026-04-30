<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Auth\Services\AuthService;

final class AuthController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(Request $request): Response
    {
        return Response::json($this->authService->placeholder('register'));
    }

    public function login(Request $request): Response
    {
        return Response::json($this->authService->placeholder('login'));
    }

    public function me(Request $request): Response
    {
        return Response::json($this->authService->placeholder('me'));
    }
}
