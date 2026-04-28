<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Plus Canvas API Documentation",
    description: "API documentation for Plus Canvas e-commerce project",
    contact: new OA\Contact(email: "admin@pluscanvas.com")
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "Local API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "apiAuth",
    type: "http",
    name: "Token Based",
    in: "header",
    bearerFormat: "JWT",
    scheme: "bearer",
    description: "Login with email and password to get the authentication token"
)]
class SwaggerController extends Controller
{
}
