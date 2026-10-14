<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TranslatesValidationMessages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminPpdbUpdateRequest extends FormRequest
{
    use TranslatesValidationMessages;

    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['Menunggu Verifikasi', 'Terverifikasi', 'Perlu Perbaikan'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->genericValidationMessages,
            'status.required' => 'Status pendaftar wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['status' => 'status pendaftar'];
    }
}
