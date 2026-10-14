<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TranslatesValidationMessages;
use Illuminate\Foundation\Http\FormRequest;

class PpdbRequest extends FormRequest
{
    use TranslatesValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{9,30}$/'],
            'origin_school' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', 'exists:programs,short'],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->genericValidationMessages,
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.max' => 'Nama lengkap maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + ( ) - dengan panjang 9-30 karakter.',
            'origin_school.required' => 'Asal sekolah wajib diisi.',
            'origin_school.max' => 'Asal sekolah maksimal 255 karakter.',
            'program.required' => 'Pilih program keahlian yang dituju.',
            'program.exists' => 'Program keahlian yang dipilih tidak terdaftar.',
            'consent.accepted' => 'Anda perlu menyetujui pernyataan persetujuan untuk melanjutkan pendaftaran.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'nama lengkap',
            'phone' => 'nomor telepon',
            'origin_school' => 'asal sekolah',
            'program' => 'program keahlian',
            'consent' => 'persetujuan',
        ];
    }
}
