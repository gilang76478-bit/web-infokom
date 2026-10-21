<?php

namespace App\Providers\Filament;

use App\Models\ContactMessage;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('SMK INFOKOM')
            // Logo sekolah dapat diganti lewat panel (Pengaturan Profil);
            // Closure agar dibaca saat render, bukan saat booting panel.
            ->brandLogo(fn () => \App\Support\SchoolSettings::logoUrl())
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/favicon-32.png'))
            ->colors([
                'primary' => Color::hex('#3446b4'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            // Semua modul konten website (CRUD khusus di luar resource
            // Filament) ikut tampil di navigasi panel, agar pengelolaan
            // guru, fasilitas, prestasi, industri, magang Jepang, alumni,
            // agenda, dan lainnya dapat dijangkau langsung dari panel admin
            // tanpa perlu mengetik URL manual.
            ->navigationItems([
                ...collect([
                    ['akademik', 'Program keahlian', Heroicon::OutlinedAcademicCap, 40],
                    ['guru', 'Guru & TTK', Heroicon::OutlinedUserGroup, 41],
                    ['fasilitas', 'Fasilitas sekolah', Heroicon::OutlinedBuildingLibrary, 42],
                    ['prestasi', 'Prestasi', Heroicon::OutlinedStar, 43],
                    ['industri', 'Mitra industri', Heroicon::OutlinedBriefcase, 44],
                    ['jepang', 'Magang Jepang', Heroicon::OutlinedGlobeAlt, 45],
                    ['reel-jepang', 'Reel Magang Jepang', Heroicon::OutlinedPlayCircle, 46],
                    ['alumni', 'Alumni', Heroicon::OutlinedUserCircle, 47],
                    ['tracer', 'Tracer study', Heroicon::OutlinedChartBar, 48],
                    ['agenda', 'Agenda', Heroicon::OutlinedCalendarDays, 49],
                    ['pengumuman', 'Pengumuman', Heroicon::OutlinedMegaphone, 50],
                    ['lowongan', 'Lowongan BKK', Heroicon::OutlinedEnvelope, 51],
                ])->map(fn (array $module) => NavigationItem::make($module[1])
                    ->icon($module[2])
                    ->sort($module[3])
                    ->url(fn (): string => route('admin.content.index', $module[0]))
                    ->isActiveWhen(fn (): bool => request()->routeIs('admin.content.*') && request()->route('type') === $module[0])
                )->all(),

                NavigationItem::make('Pengaturan sekolah')
                    ->icon(Heroicon::OutlinedCog)
                    ->sort(60)
                    ->url(fn (): string => route('admin.settings.index'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('admin.settings.*')),

                NavigationItem::make('Pendaftar PPDB')
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->sort(61)
                    ->url(fn (): string => route('admin.ppdb.index'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('admin.ppdb.*')),

                NavigationItem::make('Pesan masuk')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->sort(62)
                    ->badge(fn (): ?string => (($unread = ContactMessage::where('is_read', false)->count()) > 0) ? (string) $unread : null)
                    ->url(fn (): string => route('admin.messages.index'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('admin.messages.*')),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
