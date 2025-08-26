<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="VIP Tutors Task Management API",
 *     version="1.0.0",
 *     description="A comprehensive task management system with user authentication, task CRUD operations, reordering capabilities, and admin functionality.",
 *     @OA\Contact(
 *         email="admin@viptutors.com",
 *         name="VIP Tutors Development Team"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your bearer token in the format: Bearer {token}"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Tasks",
 *     description="Task management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Admin",
 *     description="Admin-only endpoints for user and system management"
 * )
 */
class SwaggerController extends Controller
{
    //
}
