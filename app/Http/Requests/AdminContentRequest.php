<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TranslatesValidationMessages;
use App\Support\ContentTypes;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi CRUD modul konten admin (akademik, berita, agenda, dst).
 * Aturan per tipe diambil dari ContentTypes::ALL berdasarkan {type}
 * pada route, sehingga aturan validasi tidak lagi inline di controller.
 */
class AdminContentRequest extends FormRequest
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
        return collect($this->definition()['fields'])
            ->mapWithKeys(fn (array $field, string $name) => [$name => $this->parseRules($field['rules'])])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return collect($this->definition()['fields'])
            ->mapWithKeys(fn (array $field, string $name) => [$name => $field['label']])
            ->all();
    }

    /**
     * @return array{label: string, model: class-string, fields: array<string, array<string, mixed>>}
     */
    protected function definition(): array
    {
        $type = (string) $this->route('type');

        abort_unless(isset(ContentTypes::ALL[$type]), 404);

        return ContentTypes::ALL[$type];
    }
}
