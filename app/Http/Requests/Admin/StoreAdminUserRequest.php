<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminPortalAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return AdminPortalAccess::can($this->user(), 'admin.manage_users');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(array_keys(AdminPortalAccess::assignableUserRoles()))],
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', 'exists:restaurants,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('role') === 'branch_manager' && empty($this->input('branch_ids'))) {
                $validator->errors()->add('branch_ids', 'Select at least one branch for a branch manager.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'Please select a valid role.',
        ];
    }
}
