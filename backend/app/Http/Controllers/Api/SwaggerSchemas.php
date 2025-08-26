<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="User ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="User's full name",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="User's email address",
 *         example="john@example.com"
 *     ),
 *     @OA\Property(
 *         property="is_admin",
 *         type="boolean",
 *         description="Whether the user is an admin",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="email_verified_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         description="Email verification timestamp",
 *         example="2024-01-15T10:30:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="User creation timestamp",
 *         example="2024-01-15T10:30:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="User last update timestamp",
 *         example="2024-01-15T10:30:00.000000Z"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Task",
 *     description="Task model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Task ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Task title",
 *         example="Complete project documentation"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Task description",
 *         example="Write comprehensive documentation for the project including API docs"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "completed"},
 *         description="Task status",
 *         example="pending"
 *     ),
 *     @OA\Property(
 *         property="priority",
 *         type="string",
 *         enum={"low", "medium", "high"},
 *         description="Task priority",
 *         example="medium"
 *     ),
 *     @OA\Property(
 *         property="order",
 *         type="integer",
 *         description="Task order for sorting",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID of the user who owns this task",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Task creation timestamp",
 *         example="2024-01-15T10:30:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Task last update timestamp",
 *         example="2024-01-15T10:30:00.000000Z"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Validation Error",
 *     description="Standard validation error response",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Error message",
 *         example="The given data was invalid."
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Field-specific error messages",
 *         example={
 *             "email": {"The email field is required."},
 *             "password": {"The password must be at least 8 characters."}
 *         }
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Error Response",
 *     description="Standard error response",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Error message",
 *         example="Resource not found"
 *     )
 * )
 */
class SwaggerSchemas
{
    // This class is only used for Swagger schema definitions
}
