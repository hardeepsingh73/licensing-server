<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Add policy or gate if needed for extra authorization
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id ?? null;

        return [
            'name' => [
                'required',
                'min:3',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            // 'permissions' can be validated if needed, e.g., must be array of existing permission IDs:
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }
}
