<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContactRequest extends FormRequest
{
    /**
     * Public endpoint - tidak perlu autentikasi
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:2000',
            'product_id' => 'nullable|exists:products,id',

            // Honeypot fields - must be empty (bots fill these)
            'website' => 'max:0', // hidden field, should be empty
            'fax' => 'max:0', // hidden field, should be empty

            // Timestamp validation - form must take at least 3 seconds
            '_token_time' => 'nullable|integer',
        ];
    }

    /**
     * Validate honeypot and timestamp before normal validation
     */
    protected function prepareForValidation(): void
    {
        // Check timestamp - reject if submitted too fast (< 3 seconds)
        if ($this->has('_token_time')) {
            $tokenTime = (int) $this->_token_time;
            $currentTime = time();

            // If form was submitted in less than 3 seconds, likely a bot
            if ($tokenTime > 0 && ($currentTime - $tokenTime) < 3) {
                throw new HttpResponseException(
                    ApiResponse::error('Request blocked for security reasons', 403)
                );
            }
        }

        // Honeypot check - if filled, reject silently (bots fill hidden fields)
        if (!empty($this->website) || !empty($this->fax)) {
            throw new HttpResponseException(
                ApiResponse::error('Request blocked for security reasons', 403)
            );
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.max' => 'Nama maksimal 100 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'message.required' => 'Pesan wajib diisi',
            'message.max' => 'Pesan maksimal 2000 karakter',
            'website.max' => 'Invalid request',
            'fax.max' => 'Invalid request',
        ];
    }
}

