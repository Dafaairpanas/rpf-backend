<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Handle both route model binding (User object) and plain ID (string)
        $routeParam = $this->route('user');

        if ($routeParam instanceof \App\Models\User) {
            $userId = $routeParam->id;
        } else {
            $userId = $routeParam;
        }

        return [
            'username' => "nullable|string|max:100|unique:users,username,{$userId}",
            'name' => 'nullable|string|max:100',
            'email' => "nullable|email|unique:users,email,{$userId}",
            'password' => 'nullable|string|min:6',
            'division' => 'nullable|string|max:100',
            'role_id' => 'nullable|exists:roles,id',
        ];
    }
}
