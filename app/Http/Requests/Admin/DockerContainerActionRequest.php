<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminPortalAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DockerContainerActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return AdminPortalAccess::can($this->user(), 'admin.technical.docker');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $stackIds = collect(config('docker.stacks', []))->pluck('id')->all();

        return [
            'stack_id' => ['required', 'string', Rule::in($stackIds)],
            'container' => ['required', 'string', 'max:128', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/'],
            'action' => ['required', 'string', Rule::in(['start', 'stop', 'restart'])],
        ];
    }
}
