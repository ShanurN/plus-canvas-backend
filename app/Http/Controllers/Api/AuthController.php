<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    #[OA\Post(
        path: "/api/auth/login",
        summary: "Authenticate user and return token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@pluscanvas.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "admin123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful login",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Successfully logged in"),
                        new OA\Property(property: "user", type: "object"),
                        new OA\Property(property: "token", type: "string", example: "1|AbcDef...")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            'message' => __('Successfully logged in'),
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    #[OA\Post(
        path: "/api/auth/logout",
        summary: "Logout the current user",
        tags: ["Authentication"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully logged out",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Successfully logged out")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => __('Successfully logged out'),
        ]);
    }

    #[OA\Get(
        path: "/api/auth/me",
        summary: "Get the authenticated user info",
        tags: ["Authentication"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Authenticated user profile",
                content: new OA\JsonContent(ref: "#/components/schemas/UserResource")
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
