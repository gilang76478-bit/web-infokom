<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminContentController;
use App\Http\Controllers\AdminContactController;
use App\Http\Controllers\AdminPpdbController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\BkkController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\PpdbApplicantController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SiteController;

Route::get('/', [SiteController::class, 'home'])->name('home');

// Detail program keahlian, diikat berdasarkan slug. Ditempatkan sebelum
// rute tangkap-lambung /{section} agar tidak tertangkap olehnya.
Route::get('/program/{program:slug}', [ProgramController::class, 'show'])
	->name('program.show');

// Beranda dan detail artikel berita, diikat berdasarkan slug. Ditempatkan
// sebelum rute tangkap-lambung /{section} agar tidak tertangkap olehnya.
Route::get('/berita', [PostController::class, 'index'])
	->name('berita.index');
Route::get('/berita/{post:slug}', [PostController::class, 'show'])
	->name('berita.show');

// Galeri foto dengan filter kategori, diikat di depan rute tangkap-lambung.
Route::get('/galeri', [GaleriController::class, 'index'])
	->name('galeri.index');

// Direktori guru & tenaga kependidikan, ditempatkan sebelum rute tangkap-lambung.
Route::get('/guru', [GuruController::class, 'index'])
	->name('guru.index');

// Halaman Bursa Kerja Khusus (BKK): poster lowongan + berita kategori BKK,
// ditempatkan sebelum rute tangkap-lambung /{section}.
Route::get('/bkk', [BkkController::class, 'index'])
	->name('bkk.index');

// Berkas bantu mesin pencari. robots.txt kini dinamis agar menunjuk ke
// sitemap.xml dengan domain yang benar; keduanya ditempatkan sebelum rute
// tangkap-lambung /{section} agar tidak tertangkap olehnya.
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])
	->name('sitemap');
Route::get('/robots.txt', [SiteController::class, 'robots'])
	->name('robots');

Route::post('/ppdb', [PpdbApplicantController::class, 'store'])
	->middleware('throttle:5,1')
	->name('ppdb.apply');
// Formulir pendaftaran siap cetak untuk pendaftaran langsung ke sekolah.
// Didefinisikan eksplisit agar tidak pernah bertabrakan dengan rute
// tangkap-lambung /{section}.
Route::get('/ppdb/formulir', [PpdbApplicantController::class, 'printableForm'])
	->name('ppdb.printable');
// Versi PDF formulir yang sama, diunduh sebagai berkas (bukan dialog cetak).
Route::get('/ppdb/formulir.pdf', [PpdbApplicantController::class, 'printablePdf'])
	->name('ppdb.printable.pdf');
Route::post('/kontak', [ContactMessageController::class, 'store'])
	->middleware('throttle:5,1')
	->name('contact-messages.store');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
	Route::get('/ppdb', [AdminPpdbController::class, 'index'])->name('ppdb.index');
	Route::patch('/ppdb/{applicant}', [AdminPpdbController::class, 'update'])->name('ppdb.update');
	Route::get('/pesan', [AdminContactController::class, 'index'])->name('messages.index');
	Route::patch('/pesan/{message}/read', [AdminContactController::class, 'markRead'])->name('messages.read');
	Route::get('/content/{type}', [AdminContentController::class, 'index'])->name('content.index');
	Route::get('/content/{type}/create', [AdminContentController::class, 'create'])->name('content.create');
	Route::post('/content/{type}', [AdminContentController::class, 'store'])->name('content.store');
	Route::get('/content/{type}/{record}/edit', [AdminContentController::class, 'edit'])->whereNumber('record')->name('content.edit');
	Route::put('/content/{type}/{record}', [AdminContentController::class, 'update'])->whereNumber('record')->name('content.update');
	Route::delete('/content/{type}/{record}', [AdminContentController::class, 'destroy'])->whereNumber('record')->name('content.destroy');
	Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
	Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});

Route::get('/{section}', [SiteController::class, 'section'])
	->	whereIn('section', [
		'profil', 'akademik', 'kesiswaan', 'prestasi', 'industri',
		'jepang', 'ppdb', 'informasi', 'alumni', 'kontak',
	])
	->name('site.section');
