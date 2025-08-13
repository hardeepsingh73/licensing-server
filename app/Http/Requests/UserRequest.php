<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Use policies if needed, otherwise allow
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? null; // Detect user id if updating

        return [
            'name' => 'required|string|max:255',

            // Email must be unique, but ignore current record if updating
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($userId),
            ],

            // Store = required password, Update = optional password
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            // Sometimes optional for create, always required for update (or adjust to your needs)
            'roles' => [
                $this->isMethod('post') ? 'sometimes' : 'required',
                'string',
                Rule::exists('roles', 'name'),
            ],
        ];
    }
}
