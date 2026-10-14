<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TranslatesValidationMessages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'whatsapp' => ['required', 'string', 'regex:/^[0-9+()\-\s]{9,30}$/'],
            'subject' => ['required', Rule::in(config('school.contact.inquiry_types'))],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            // Honeypot: robot yang mengisi kolom tersembunyi ini ditolak.
            'company_website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->genericValidationMessages,
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 160 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 190 karakter.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp hanya boleh berisi angka, spasi, dan tanda + ( ) - dengan panjang 9-30 karakter.',
            'subject.required' => 'Pilih salah satu keperluan yang tersedia.',
            'subject.in' => 'Keperluan yang dipilih tidak valid.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan minimal 10 karakter.',
            'message.max' => 'Pesan maksimal 5000 karakter.',
            'company_website.prohibited' => 'Permintaan ditolak.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'whatsapp' => 'nomor WhatsApp',
            'subject' => 'subjek',
            'message' => 'pesan',
        ];
    }
}
