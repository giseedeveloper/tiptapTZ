<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'menu_pdf' => ['required', 'file', 'mimes:pdf', 'max:15360'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'menu_pdf.required' => 'Please choose a PDF file to upload.',
            'menu_pdf.mimes' => 'The menu must be a PDF file.',
            'menu_pdf.max' => 'The menu PDF may not be larger than 15 MB.',
        ];
    }
}
