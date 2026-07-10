<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\AdminPortalAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminUserRequest extends FormRequest
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
        $userId = $this->route('user') instanceof User
            ? $this->route('user')->id
            : $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
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
}
