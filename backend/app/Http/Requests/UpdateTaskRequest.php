<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $task = $this->route('task');
        
        // Allow if user is admin or owns the task
        return auth()->user()->isAdmin() || 
               ($task && $task->user_id === auth()->id());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:pending,completed'],
            'priority' => ['sometimes', 'in:low,medium,high'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.string' => 'Task title must be a valid string.',
            'title.max' => 'Task title must not exceed 255 characters.',
            'description.string' => 'Task description must be a valid string.',
            'description.max' => 'Task description must not exceed 1000 characters.',
            'status.in' => 'Status must be either pending or completed.',
            'priority.in' => 'Priority must be low, medium, or high.',
            'order.integer' => 'Order must be a valid number.',
            'order.min' => 'Order must be 0 or greater.',
        ];
    }
}
