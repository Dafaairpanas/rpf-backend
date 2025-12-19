<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        return [
            'username' => 'nullable|string|max:100|unique:users,username',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'division' => 'nullable|string|max:100',
            'role_id' => 'nullable|exists:roles,id',
        ];
    }
}
