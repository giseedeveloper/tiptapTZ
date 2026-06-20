<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminPortalAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return AdminPortalAccess::can($this->user(), 'admin.manage_roles');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'string',
                Rule::in(AdminPortalAccess::permissionNames()),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $role = $this->route('role');

        if (! is_string($role) || ! in_array($role, AdminPortalAccess::editableRoles(), true)) {
            abort(403, 'This role cannot be edited.');
        }
    }
}
