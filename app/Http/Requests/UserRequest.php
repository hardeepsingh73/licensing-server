<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For create requests, check if user can create users
        if ($this->isMethod('post')) {
            return $this->user()->can('create', User::class);
        }

        // For update/delete requests, check if user can manage the target user
        $targetUser = $this->route('user');
        return $this->user()->can('manage', $targetUser);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'roles' => [
                $this->isMethod('post') ? 'sometimes' : 'required',
                'string',
                Rule::exists('roles', 'name'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',

            'email.required' => 'The email address is required.',
            'email.email' => 'The email address must be a valid email format.',
            'email.unique' => 'This email address is already registered.',

            'password.required' => 'A password is required.',
            'password.string' => 'The password must be a valid string.',
            'password.min' => 'The password must be at least :min characters.',
            'password.confirmed' => 'The password confirmation does not match.',

            'roles.required' => 'A role is required.',
            'roles.string' => 'The role must be provided as a valid string.',
            'roles.exists' => 'The selected role does not exist in the system.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
            'roles' => 'user role',
        ];
    }
}
