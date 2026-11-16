<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:100'],
            'url'     => ['required', 'url:http,https', 'max:255'],
            'method'  => ['nullable', 'string', 'in:GET,POST,HEAD'],
            'headers' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama layanan wajib diisi.',
            'url.required'  => 'Alamat URL wajib diisi.',
            'url.url'       => 'Format URL tidak valid (harus diawali http:// atau https://).',
        ];
    }
}