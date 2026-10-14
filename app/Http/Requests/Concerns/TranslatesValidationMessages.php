<?php

namespace App\Http\Requests\Concerns;

/**
 * Pesan validasi berbahasa Indonesia untuk aturan yang paling umum.
 *
 * Setiap Form Request boleh menimpa atau menambah pesan per atribut
 * dengan format 'atribut.aturan' (mis. 'name.required'), dan
 * mengatur nama atribut lewat attributes() agar pesan generik
 * terbaca alami tanpa istilah teknis dari framework.
 */
trait TranslatesValidationMessages
{
    /** @var array<string, string> */
    protected array $genericValidationMessages = [
        'required' => 'Kolom :attribute wajib diisi.',
        'string' => 'Kolom :attribute harus berupa teks.',
        'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
        'numeric' => 'Kolom :attribute harus berupa angka.',
        'email' => 'Format :attribute tidak valid.',
        'url' => 'Kolom :attribute harus berupa tautan yang valid.',
        'date' => 'Kolom :attribute harus berupa tanggal yang valid.',
        'max' => 'Kolom :attribute melebihi batas maksimum (:max).',
        'min' => 'Kolom :attribute belum mencapai batas minimum (:min).',
        'in' => 'Pilihan untuk :attribute tidak valid.',
        'exists' => 'Pilihan :attribute tidak terdaftar.',
        'accepted' => 'Kolom :attribute harus disetujui.',
        'prohibited' => 'Kolom :attribute tidak boleh diisi.',
        'regex' => 'Format :attribute tidak sesuai ketentuan.',
    ];

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->genericValidationMessages;
    }

    /**
     * Mengubah definisi aturan ('required|string|max:255' atau array)
     * menjadi array aturan siap pakai untuk validator.
     *
     * @return array<int, mixed>
     */
    protected function parseRules(string | array $rules): array
    {
        if (is_array($rules)) {
            return $rules;
        }

        return explode('|', $rules);
    }
}
