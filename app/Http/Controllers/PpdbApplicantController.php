<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpdbRequest;
use App\Models\PpdbApplicant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PpdbApplicantController extends Controller
{
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