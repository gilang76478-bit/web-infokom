<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpdbRequest;
use App\Models\PpdbApplicant;
use App\Models\Program;
use App\Support\SchoolSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PpdbApplicantController extends Controller
{
    /**
     * Halaman formulir pendaftaran siap cetak. Ditujukan untuk calon murid
     * atau orang tua yang ingin mendaftar langsung ke sekolah tanpa lewat
     * formulir daring: cetak halaman ini, isi dengan huruf cetak, lalu
     * serahkan ke panitia bersama dokumen persyaratan.
     */
    public function printableForm(): View
    {
        return view('ppdb.printable', [
            'contact' => SchoolSettings::contact(),
            'spmb' => SchoolSettings::ppdb(),
            'programs' => Program::orderBy('short')->get(),
        ]);
    }

    /**
     * Versi PDF halaman formulir siap cetak: diunduh sebagai berkas agar
     * calon murid atau orang tua dapat menyimpannya tanpa membuka halaman
     * cetak peramban. Memakai view terpisah dengan tata letak tabel yang
     * ramah dompdf, dan nama berkasnya stabil agar mudah dikenali.
     */
    public function printablePdf(): Response
    {
        return Pdf::loadView('ppdb.printable-pdf', [
            'contact' => SchoolSettings::contact(),
            'spmb' => SchoolSettings::ppdb(),
            'programs' => Program::orderBy('short')->get(),
        ])
            ->setPaper('a4')
            ->download('formulir-ppdb-smk-infokom.pdf');
    }
    public function store(PpdbRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('consent');

        do {
            $number = 'INF-' . now()->format('Y') . '-' . Str::upper(Str::random(6));
        } while (PpdbApplicant::where('registration_number', $number)->exists());

        $applicant = PpdbApplicant::create([
            ...$data,
            'registration_number' => $number,
            'status' => 'Menunggu Verifikasi',
        ]);

        return back()->with('registration_number', $applicant->registration_number);
    }
}