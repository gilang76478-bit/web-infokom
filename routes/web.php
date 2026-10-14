<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminContentController;
use App\Http\Controllers\AdminContactController;
use App\Http\Controllers\AdminPpdbController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\PpdbApplicantController;
use App\Http\Controllers\SiteController;

Route::get('/', [SiteController::class, 'home'])->name('home');

Route::post('/ppdb', [PpdbApplicantController::class, 'store'])
	->middleware('throttle:5,1')
	->name('ppdb.apply');
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
	->whereIn('section', [
		'profil', 'akademik', 'kesiswaan', 'prestasi', 'industri',
		'jepang', 'ppdb', 'informasi', 'galeri', 'alumni', 'kontak',
	])
	->name('site.section');
