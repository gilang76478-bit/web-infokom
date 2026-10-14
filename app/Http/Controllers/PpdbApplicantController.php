<?php

namespace App\Http\Controllers;

use App\Models\PpdbApplicant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PpdbApplicantController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{9,30}$/'],
            'origin_school' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', 'exists:programs,short'],
            'consent' => ['accepted'],
        ]);
        unset($data['consent']);

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