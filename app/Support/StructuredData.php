<?php

namespace App\Support;

/**
 * Data terstruktur Schema.org (JSON-LD) untuk halaman publik.
 *
 * Node "School" menceritakan identitas sekolah kepada mesin pencari dan
 * dipasang pada setiap halaman; "BreadcrumbList" menandai posisi sebelas
 * bagian situs agar hasil pencarian menampilkan remah navigasi.
 * Seluruh properti yang nilainya kosong sengaja tidak ditembak ke JSON
 * agar mesin pencari tidak menerima nilai kosong atau placeholder.
 */
class StructuredData
{
    /**
     * Node sekolah (Schema.org/School) untuk halaman mana pun.
     * Hanya properti yang nilainya benar-benar terisi yang disertakan.
     *
     * @return array<string, mixed>
     */
    public static function school(): array
    {
        $contact = SchoolSettings::contact();
        $profile = SchoolSettings::profile();

        $address = collect($contact['address'] ?? [])
            ->filter(fn ($value) => filled($value));

        $node = [
            '@context' => 'https://schema.org',
            '@type' => 'School',
            '@id' => route('home').'#sekolah',
            'name' => filled($contact['name'] ?? null) ? $contact['name'] : 'SMK INFOKOM',
            'url' => route('home'),
            'logo' => SchoolSettings::logoUrl('images/logo-512.png'),
            'inLanguage' => 'id',
        ];

        // Alamat pos hanya dibentuk bila baris utamanya terisi; properti
        // kosong dibuang agar JSON-LD tetap bersih.
        if (filled($address->get('Alamat lengkap'))) {
            $node['address'] = array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => collect([
                    $address->get('Alamat lengkap'),
                    $address->get('RT/RW') ? 'RT/RW '.$address->get('RT/RW') : null,
                ])->filter()->implode(', '),
                'addressLocality' => $address->get('Kota/Kabupaten'),
                'addressRegion' => $address->get('Provinsi'),
                'postalCode' => $address->get('Kode pos'),
                'addressCountry' => 'ID',
            ], fn ($value) => filled($value));
        }

        if (filled($contact['phones']['general'] ?? null)) {
            $node['telephone'] = $contact['phones']['general'];
        }

        if (filled($contact['emails']['general'] ?? null)) {
            $node['email'] = $contact['emails']['general'];
        }

        if (filled($contact['maps_url'] ?? null)) {
            $node['hasMap'] = $contact['maps_url'];
        }

        $socials = collect($contact['socials'] ?? [])
            ->filter(fn ($url) => filled($url))
            ->values()
            ->all();

        if ($socials !== []) {
            $node['sameAs'] = $socials;
        }

        if (filled($profile['founded_year'] ?? null)) {
            $node['foundingDate'] = $profile['founded_year'];
        }

        // Titik kontak hanya dibentuk bila nomor atau email terisi.
        $contactPoints = collect([
            ['contactType' => 'customer service', 'phone' => $contact['phones']['general'] ?? null, 'email' => $contact['emails']['general'] ?? null],
            ['contactType' => 'Penerimaan murid baru (PPDB)', 'phone' => $contact['phones']['ppdb'] ?? null, 'email' => $contact['emails']['ppdb'] ?? null],
        ])
            ->filter(fn (array $point) => filled($point['phone']) || filled($point['email']))
            ->map(fn (array $point) => array_filter([
                '@type' => 'ContactPoint',
                'contactType' => $point['contactType'],
                'telephone' => $point['phone'],
                'email' => $point['email'],
                'areaServed' => 'ID',
                'availableLanguage' => ['id', 'en'],
            ], fn ($value) => filled($value)))
            ->values()
            ->all();

        if ($contactPoints !== []) {
            $node['contactPoint'] = $contactPoints;
        }

        return $node;
    }

    /**
     * Remah navigasi (Schema.org/BreadcrumbList) untuk sebuah halaman.
     * Posisi 1 selalu Beranda, dilanjutkan induk opsional (mis. "Berita &
     * Artikel"), dan posisi terakhir adalah halaman yang sedang dibuka.
     * Nomor posisi dihitung otomatis agar jejak tiga tingkat tetap benar.
     *
     * @param  array<int, array{name: string, item: string}>  $parents
     * @return array<string, mixed>
     */
    public static function breadcrumb(string $title, string $url, array $parents = []): array
    {
        $trail = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Beranda',
                'item' => route('home'),
            ],
        ];

        foreach ($parents as $parent) {
            $trail[] = [
                '@type' => 'ListItem',
                'position' => count($trail) + 1,
                'name' => $parent['name'],
                'item' => $parent['item'],
            ];
        }

        $trail[] = [
            '@type' => 'ListItem',
            'position' => count($trail) + 1,
            'name' => $title,
            'item' => $url,
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $trail,
        ];
    }

    /**
     * Node artikel (Schema.org/Article) untuk halaman detail berita.
     *
     * Gambar memakai foto artikel bila tersedia, dan kembali ke og:image
     * bawaan (logo sekolah) untuk artikel tanpa foto. Author dan publisher
     * dirujuk ke node School
     * lewat @id agar identitas sekolah tetap bersumber dari satu node, dan
     * hanya properti terisi yang dilempar ke JSON.
     *
     * @return array<string, mixed>
     */
    public static function article(
        string $title,
        string $url,
        string $description,
        string $datePublished,
        ?string $dateModified = null,
        ?string $section = null,
        ?string $image = null,
    ): array {
        $school = self::school();

        $identity = [
            '@type' => 'School',
            '@id' => $school['@id'],
            'name' => $school['name'],
        ];

        $node = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'image' => $image ?: SchoolSettings::logoUrl('images/logo-512.png'),
            'url' => $url,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $url,
            ],
            'datePublished' => $datePublished,
            'author' => $identity,
            'publisher' => [
                ...$identity,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => SchoolSettings::logoUrl('images/logo-512.png'),
                    'width' => 512,
                    'height' => 512,
                ],
            ],
            'inLanguage' => 'id',
        ];

        if (filled($dateModified)) {
            $node['dateModified'] = $dateModified;
        }

        if (filled($section)) {
            $node['articleSection'] = $section;
        }

        return $node;
    }

    /**
     * Node FAQ (Schema.org/FAQPage) untuk halaman penerimaan murid baru.
     * Hanya dipasang ketika daftar pertanyaan benar-benar terisi sehingga
     * mesin pencari tidak menerima blok kosong.
     *
     * @param  array<int, array{question: string, answer: string}>  $faq
     * @return array<string, mixed>
     */
    public static function faqPage(array $faq, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'url' => $url,
            'mainEntity' => collect($faq)
                ->map(fn (array $item) => [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * Merangkai tiap node menjadi satu blok <script type="application/ld+json">
     * terpisah — satu objek per blok agar mesin pencari membaca tiap node
     * secara mandiri (bukan sebagai array tingkat atas).
     * JSON_HEX_TAG mencegah sisipan "</script>" dari data admin memutus blok skrip.
     */
    public static function script(array ...$nodes): string
    {
        return collect($nodes)
            ->map(fn (array $node) => '<script type="application/ld+json">'
                .json_encode($node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
                .'</script>')
            ->implode('');
    }
}
