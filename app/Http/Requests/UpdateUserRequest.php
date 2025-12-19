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
        $userId = $this->route('user') ? $this->route('user')->id : null;
        // Jika route model binding belum resolved, coba ambil parameter ID langsung
        if (! $userId) {
            $userId = $this->route('user');
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
