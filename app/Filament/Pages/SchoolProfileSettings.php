<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\SchoolSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;

/**
 * Form pengaturan konten halaman profil (/profil).
 *
 * Semua nilai disimpan sebagai baris teks biasa di tabel site_settings
 * (satu baris = satu paragraf / satu poin misi / satu fasilitas / satu
 * tonggak sejarah), sehingga identik dengan cara membaca yang dilakukan
 * SchoolSettings::profile().
 *
 * @property-read Schema $form
 */
class SchoolProfileSettings extends Page
{
    /** @var array<string, mixed> */
    public array $data = [];

    protected static ?string $slug = 'pengaturan-profil';

    protected static ?string $navigationLabel = 'Pengaturan Profil';

    protected static ?string $title = 'Pengaturan Profil Sekolah';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocument;

    protected static ?int $navigationSort = 30;

    public function mount(): void
    {
        $profile = SchoolSettings::profile();

        $this->form->fill([
            'status' => $profile['status'],
            'npsn' => $profile['npsn'],
            'founded_year' => $profile['founded_year'],
            'accreditation' => $profile['accreditation'],
            'principal_name' => $profile['principal_name'],
            'principal_title' => $profile['principal_title'],
            'sambutan_title' => $profile['sambutan_title'],
            'sambutan_text' => implode("\n", $profile['sambutan_paragraphs']),
            'vision' => $profile['vision'],
            'mission' => implode("\n", $profile['mission']),
            'facilities' => collect($profile['facilities'])
                ->map(fn (array $facility) => $facility['title'].'|'.$facility['description'])
                ->implode("\n"),
            'history' => collect($profile['history'])
                ->map(fn (array $entry) => implode('|', [$entry['year'], $entry['title'], $entry['description']]))
                ->implode("\n"),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas sekolah')
                    ->description('Tampil pada kartu Identitas Sekolah di halaman /profil.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('status')
                            ->label('Status sekolah')
                            ->placeholder('Swasta')
                            ->maxLength(40),
                        TextInput::make('npsn')
                            ->label('NPSN')
                            ->placeholder('Belum dikonfirmasi')
                            ->maxLength(20),
                        TextInput::make('founded_year')
                            ->label('Tahun berdiri')
                            ->placeholder('Belum dikonfirmasi')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(2100),
                        TextInput::make('accreditation')
                            ->label('Akreditasi')
                            ->placeholder('Belum dikonfirmasi')
                            ->maxLength(120),
                        TextInput::make('principal_name')
                            ->label('Nama kepala sekolah')
                            ->placeholder('Informasi belum tersedia')
                            ->maxLength(255),
                        TextInput::make('principal_title')
                            ->label('Jabatan kepala sekolah')
                            ->placeholder('Kepala SMK INFOKOM')
                            ->maxLength(255),
                    ]),

                Section::make('Sambutan kepala sekolah')
                    ->description('Satu baris = satu paragraf. Kosongkan untuk mengembalikan teks bawaan.')
                    ->schema([
                        TextInput::make('sambutan_title')
                            ->label('Judul sambutan')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('sambutan_text')
                            ->label('Isi sambutan (satu paragraf per baris)')
                            ->rows(8)
                            ->maxLength(8000),
                    ]),

                Section::make('Visi & misi')
                    ->description('Satu baris pada kolom misi = satu poin misi yang ditampilkan berurutan.')
                    ->schema([
                        Textarea::make('vision')
                            ->label('Visi')
                            ->rows(3)
                            ->required()
                            ->maxLength(2000),
                        Textarea::make('mission')
                            ->label('Misi (satu poin per baris)')
                            ->rows(7)
                            ->maxLength(8000),
                    ]),

                Section::make('Sejarah sekolah')
                    ->description('Satu baris = satu tonggak dengan format "Tahun|Judul|Keterangan". Kosongkan untuk mengembalikan keterangan bawaan.')
                    ->schema([
                        Textarea::make('history')
                            ->label('Tonggak sejarah (satu baris per tonggak)')
                            ->rows(7)
                            ->maxLength(4000),
                    ]),

                Section::make('Fasilitas')
                    ->description('Satu baris = satu fasilitas dengan format "Nama fasilitas|Deskripsi".')
                    ->schema([
                        Textarea::make('facilities')
                            ->label('Daftar fasilitas')
                            ->rows(10)
                            ->maxLength(8000),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Simpan pengaturan')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ])
                            ->alignment(Alignment::Start)
                            ->key('form-actions'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $textKeys = [
            'status' => 'profile_status',
            'npsn' => 'profile_npsn',
            'founded_year' => 'profile_founded_year',
            'accreditation' => 'profile_accreditation',
            'principal_name' => 'profile_principal_name',
            'principal_title' => 'profile_principal_title',
            'sambutan_title' => 'profile_sambutan_title',
            'vision' => 'profile_vision',
        ];

        $lineKeys = [
            'sambutan_text' => 'profile_sambutan_text',
            'mission' => 'profile_mission',
            'facilities' => 'profile_facilities',
            'history' => 'profile_history',
        ];

        foreach ($textKeys as $field => $dbKey) {
            $this->store($dbKey, (string) ($data[$field] ?? ''));
        }

        foreach ($lineKeys as $field => $dbKey) {
            $value = collect(preg_split('/\r\n|\r|\n/', (string) ($data[$field] ?? '')))
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->implode("\n");

            $this->store($dbKey, $value);
        }

        Notification::make()
            ->title('Pengaturan profil sekolah disimpan')
            ->success()
            ->send();
    }

    protected function store(string $key, string $value): void
    {
        if ($value === '') {
            SiteSetting::where('key', $key)->delete();

            return;
        }

        SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
