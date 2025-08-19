<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseKeyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get validation rules that apply to the request.
     */
    public function rules(): array
    {
        $licenseKeyId = $this->route('license') ? $this->route('license')->id : null;

        return [
            'key' => [
                'required',
                'string',
                'max:255',
                'unique:license_keys,key' . ($licenseKeyId ? ',' . $licenseKeyId : ''),
            ],
            'status' => 'required|integer',
            'activation_limit' => 'required|integer|min:1',
            'expires_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'key' => 'license key',
            'status' => 'license status',
            'activation_limit' => 'activation limit',
            'expires_at' => 'expiry date',
        ];
    }
}
