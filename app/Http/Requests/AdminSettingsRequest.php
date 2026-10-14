<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TranslatesValidationMessages;
use App\Support\SchoolSettingFields;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi halaman Pengaturan Sekolah. Daftar aturan dan nama atribut
 * diambil dari SchoolSettingFields sehingga label di pesan error selalu
 * sama dengan label di form admin.
 */
class AdminSettingsRequest extends FormRequest
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
        return collect(SchoolSettingFields::ALL)
            ->mapWithKeys(fn (array $field, string $key) => [$key => $this->parseRules($field['rules'])])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return collect(SchoolSettingFields::ALL)
            ->mapWithKeys(fn (array $field, string $key) => [$key => $field['label']])
            ->all();
    }
}
