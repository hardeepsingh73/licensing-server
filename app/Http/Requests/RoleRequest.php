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
    public function messages(): array
    {
        return [
            'name.required' => 'The role name is required.',
            'name.min' => 'The role name must be at least :min characters.',
            'name.unique' => 'This role name is already in use.',
            'permissions.array' => 'The permissions must be provided as an array.',
            'permissions.*.integer' => 'Each permission ID must be an integer.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'role name',
            'permissions' => 'permissions',
            'permissions.*' => 'permission'
        ];
    }
}
