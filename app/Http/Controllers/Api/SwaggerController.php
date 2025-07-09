<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


/**
 * @OA\Info(
 *     title="API HUB-UMKM Documentation",
 *     version="1.0.0",
 *     description="Documentation for HUB PHB UMKM",
 *     @OA\Contact(
 *         email="jamal.apriadi@gmail.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Laravel Swagger API server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="passport",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\Get(
 *     path="/api/user",
 *     tags={"Auth"},
 *     summary="Get authenticated user data",
 *     description="Mengambil data user yang sedang login melalui token Passport.",
 *     operationId="getAuthenticatedUser",
 *     security={{"passport": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="User data retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Jamal Apriadi"),
 *             @OA\Property(property="email", type="string", example="jamal@example.com"),
 *             @OA\Property(property="phone_number", type="string", example="08345353"),
 *             @OA\Property(property="address", type="string", example=""),
 *             @OA\Property(property="city", type="string", example=""),
 *             @OA\Property(property="province", type="string", example=""),
 *             @OA\Property(property="postal_code", type="string", example=""),
 *             @OA\Property(property="role", type="string", example=""),
 *             @OA\Property(property="email_verified_at", type="string", example=""),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized (unauthenticated)"
 *     )
 * )
 */
class SwaggerController extends Controller
{
    //
}
