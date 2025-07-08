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
 */
class SwaggerController extends Controller
{
    //
}
