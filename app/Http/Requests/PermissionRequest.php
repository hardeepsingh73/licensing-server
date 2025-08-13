<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Add gate or policy here if needed
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id ?? null;

        return [
            'name' => [
                'required',
                'min:3',
                Rule::unique('permissions', 'name')->ignore($permissionId),
            ],
        ];
    }
}
